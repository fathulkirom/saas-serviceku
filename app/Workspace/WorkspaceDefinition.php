<?php

namespace App\Workspace;

/**
 * WorkspaceDefinition
 * 
 * Defines the structure of an Enterprise Workspace.
 * One definition per module (Service, Inventory, POS, Finance, etc.).
 */
class WorkspaceDefinition
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $icon = '',
        public readonly array $tabs = [],
        public readonly array $actions = [],
        public readonly array $sidebarWidgets = [],
        public readonly array $toolbarActions = [],
        public readonly array $inspectorSections = [],
        public readonly array $shortcuts = [],
        public readonly array $roles = [],           // empty = all
        public readonly array $permissions = [],     // empty = no gate
        public readonly array $features = [],        // empty = no gate
        public readonly array $businessTypes = [],   // empty = all
        public readonly array $denyBusinessTypes = [],
        public readonly array $config = [],
    ) {}

    /**
     * Check if this workspace is accessible for given context.
     */
    public function isAccessible(
        string $userRole,
        array $planAccess,
        array $rolePermissions,
        string $businessType,
    ): bool {
        // Role gate
        if (!empty($this->roles) && !in_array($userRole, $this->roles)) return false;

        // Permission gate
        if (!empty($this->permissions)) {
            $hasPermission = false;
            foreach ($this->permissions as $perm) {
                if (in_array($perm, $rolePermissions)) { $hasPermission = true; break; }
            }
            if (!$hasPermission) return false;
        }

        // Feature gate
        if (!empty($this->features)) {
            $hasFeature = false;
            foreach ($this->features as $feature) {
                $level = $planAccess[$feature] ?? 'none';
                if ($level === 'full' || $level === 'read_only') { $hasFeature = true; break; }
            }
            if (!$hasFeature) return false;
        }

        // Business type deny
        if (!empty($this->denyBusinessTypes) && in_array($businessType, $this->denyBusinessTypes)) return false;

        // Business type allow
        if (!empty($this->businessTypes) && !in_array($businessType, $this->businessTypes)) return false;

        return true;
    }

    /**
     * Resolve actions filtered by user context.
     */
    public function resolveActions(string $userRole): array
    {
        return array_values(array_filter($this->actions, function ($action) use ($userRole) {
            $actionRoles = $action['roles'] ?? [];
            if (empty($actionRoles)) return true;
            return in_array($userRole, $actionRoles);
        }));
    }

    /**
     * Resolve tabs filtered by user context + data availability.
     */
    public function resolveTabs(string $userRole, array $dataContext = []): array
    {
        return array_values(array_filter($this->tabs, function ($tab) use ($userRole) {
            $tabRoles = $tab['roles'] ?? [];
            if (empty($tabRoles)) return true;
            return in_array($userRole, $tabRoles);
        }));
    }

    /**
     * Resolve sidebar widgets for user role.
     */
    public function resolveSidebar(string $userRole): array
    {
        return array_values(array_filter($this->sidebarWidgets, function ($widget) use ($userRole) {
            $widgetRoles = $widget['roles'] ?? [];
            if (empty($widgetRoles)) return true;
            return in_array($userRole, $widgetRoles);
        }));
    }

    /**
     * Convert to array for frontend.
     */
    public function toArray(string $userRole, array $planAccess, array $rolePermissions, string $businessType): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'icon' => $this->icon,
            'tabs' => $this->resolveTabs($userRole),
            'actions' => $this->resolveActions($userRole),
            'sidebarWidgets' => $this->resolveSidebar($userRole),
            'toolbarActions' => $this->toolbarActions,
            'inspectorSections' => $this->inspectorSections,
            'shortcuts' => $this->shortcuts,
            'config' => $this->config,
            'accessible' => $this->isAccessible($userRole, $planAccess, $rolePermissions, $businessType),
        ];
    }
}
