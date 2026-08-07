<?php

namespace App\Workspace;

/**
 * WorkspaceRegistry
 * 
 * Central registry for all Enterprise Workspaces.
 * Modules register their workspace definitions here.
 */
class WorkspaceRegistry
{
    /** @var WorkspaceDefinition[] */
    protected array $workspaces = [];

    /**
     * Register a workspace definition.
     */
    public function register(WorkspaceDefinition $definition): self
    {
        $this->workspaces[$definition->id] = $definition;
        return $this;
    }

    /**
     * Register multiple workspace definitions.
     */
    public function registerAll(array $definitions): self
    {
        foreach ($definitions as $definition) {
            $this->register($definition);
        }
        return $this;
    }

    /**
     * Get a workspace by ID.
     */
    public function get(string $id): ?WorkspaceDefinition
    {
        return $this->workspaces[$id] ?? null;
    }

    /**
     * Get all registered workspaces.
     * @return WorkspaceDefinition[]
     */
    public function all(): array
    {
        return $this->workspaces;
    }

    /**
     * Get workspaces accessible by current user context.
     * @return WorkspaceDefinition[]
     */
    public function accessible(string $userRole, array $planAccess, array $rolePermissions, string $businessType): array
    {
        return array_filter($this->workspaces, fn($ws) =>
            $ws->isAccessible($userRole, $planAccess, $rolePermissions, $businessType)
        );
    }

    /**
     * Resolve a full workspace config for frontend consumption.
     */
    public function resolve(string $id, string $userRole, array $planAccess, array $rolePermissions, string $businessType, array $dataContext = []): ?array
    {
        $ws = $this->get($id);
        if (!$ws) return null;
        if (!$ws->isAccessible($userRole, $planAccess, $rolePermissions, $businessType)) return null;

        return $ws->toArray($userRole, $planAccess, $rolePermissions, $businessType);
    }

    /**
     * Check if a workspace ID is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->workspaces[$id]);
    }

    /**
     * Get count of registered workspaces.
     */
    public function count(): int
    {
        return count($this->workspaces);
    }
}
