<?php

namespace App\Services;

use App\Models\GoogleDriveToken;
use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleDrivePhotoService
{
    protected ?GoogleClient $client = null;
    protected ?Drive $driveService = null;
    protected ?GoogleDriveToken $token = null;

    public function __construct(?string $tenantId = null)
    {
        if ($tenantId) {
            $this->token = GoogleDriveToken::where('tenant_id', $tenantId)->first();
        }

        if ($this->token && $this->token->access_token) {
            if (config('services.google.client_id') && config('services.google.drive_redirect')) {
                try {
                    $this->initClient();
                } catch (\Exception $e) {
                    Log::warning('GoogleDrivePhotoService init failed: ' . $e->getMessage());
                    $this->client = null;
                    $this->driveService = null;
                }
            }
        }
    }

    protected function initClient(): void
    {
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.drive_redirect'));
        $this->client->setScopes([Drive::DRIVE_FILE]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        $this->client->setAccessToken([
            'access_token' => $this->token->access_token,
            'refresh_token' => $this->token->refresh_token,
            'expires_in' => $this->token->token_expiry ? $this->token->token_expiry->diffInSeconds(now()) : 3600,
        ]);

        if ($this->client->isAccessTokenExpired()) {
            $this->refreshToken();
        }

        $this->driveService = new Drive($this->client);
    }

    protected function refreshToken(): void
    {
        try {
            $credentials = $this->client->fetchAccessTokenWithRefreshToken();
            if (!empty($credentials['access_token'])) {
                $this->token->update([
                    'access_token' => $credentials['access_token'],
                    'token_expiry' => now()->addSeconds($credentials['expires_in'] ?? 3600),
                ]);
                $this->client->setAccessToken($credentials['access_token']);
            }
        } catch (\Exception $e) {
            Log::error('Google Drive token refresh failed: ' . $e->getMessage());
            $this->token->update(['access_token' => null]);
        }
    }

    public function getAuthUrl(): string
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.drive_redirect');
        if (!$clientId || !$redirectUri) {
            return '';
        }

        $client = new GoogleClient();
        $client->setClientId($clientId);
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri($redirectUri);
        $client->setScopes([Drive::DRIVE_FILE]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client->createAuthUrl();
    }

    public function handleCallback(string $authCode, string $tenantId): array
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.drive_redirect'));

        $credentials = $client->fetchAccessTokenWithAuthCode($authCode);

        if (empty($credentials['access_token'])) {
            throw new \Exception('Failed to get access token');
        }

        $client->setAccessToken($credentials['access_token']);
        $drive = new Drive($client);
        $about = $drive->about->get(['fields' => 'user']);

        $token = GoogleDriveToken::updateOrCreate(
            ['tenant_id' => $tenantId],
            [
                'access_token' => $credentials['access_token'],
                'refresh_token' => $credentials['refresh_token'] ?? null,
                'token_expiry' => now()->addSeconds($credentials['expires_in'] ?? 3600),
                'connected_email' => $about->user->emailAddress ?? null,
            ]
        );

        $this->ensureFolderStructure($drive, $token);

        return ['connected_email' => $token->connected_email];
    }

    protected function ensureFolderStructure(Drive $drive, GoogleDriveToken $token): void
    {
        if ($token->root_folder_id) {
            return;
        }

        $folder = new DriveFile();
        $folder->setName('ServiceKU');
        $folder->setMimeType('application/vnd.google-apps.folder');

        $createdFolder = $drive->files->create($folder, ['fields' => 'id']);
        $token->update(['root_folder_id' => $createdFolder->id]);
    }

    public function upload(string $localPath, string $fileName, string $subFolder = ''): ?string
    {
        if (!$this->driveService || !$this->token) {
            return null;
        }

        try {
            $parentId = $this->token->root_folder_id;

            if ($subFolder) {
                $parentId = $this->findOrCreateFolder($subFolder, $parentId);
            }

            $file = new DriveFile();
            $file->setName($fileName);
            $file->setParents([$parentId]);

            $mimeType = mime_content_type($localPath);
            $uploaded = $this->driveService->files->create(
                $file,
                [
                    'data' => file_get_contents($localPath),
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart',
                    'fields' => 'id,webViewLink',
                ]
            );

            return $uploaded->webViewLink ?? 'https://drive.google.com/file/d/' . $uploaded->id;
        } catch (\Exception $e) {
            Log::error('Google Drive upload failed: ' . $e->getMessage());
            return null;
        }
    }

    protected function findOrCreateFolder(string $name, string $parentId): string
    {
        $query = "mimeType='application/vnd.google-apps.folder' and name='{$name}' and '{$parentId}' in parents and trashed=false";
        $results = $this->driveService->files->listFiles(['q' => $query, 'fields' => 'files(id)']);

        if (count($results->files) > 0) {
            return $results->files[0]->id;
        }

        $folder = new DriveFile();
        $folder->setName($name);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents([$parentId]);

        $created = $this->driveService->files->create($folder, ['fields' => 'id']);
        return $created->id;
    }

    public function disconnect(): void
    {
        if ($this->token) {
            $this->token->delete();
        }
    }

    public function isConnected(): bool
    {
        return $this->token && $this->token->access_token;
    }

    public function getConnectionInfo(): ?array
    {
        if (!$this->token) {
            return null;
        }

        return [
            'connected_email' => $this->token->connected_email,
            'connected_at' => $this->token->created_at,
            'is_expired' => $this->token->token_expiry && $this->token->token_expiry->isPast(),
        ];
    }
}
