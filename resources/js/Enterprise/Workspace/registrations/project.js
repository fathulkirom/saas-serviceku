import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import ProjectOverview from '@/Pages/Project/sections/Overview.vue';

workspaceRegistry.register('project', {
  tabs: { overview: ProjectOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    edit(data) { window.dispatchEvent(new CustomEvent('project:edit', { detail: data })); },
    add_task(data) { window.dispatchEvent(new CustomEvent('project:add-task')); },
    add_milestone(data) { window.dispatchEvent(new CustomEvent('project:add-milestone')); },
    allocate_resource(data) { window.dispatchEvent(new CustomEvent('project:allocate-resource')); },
    add_risk(data) { window.dispatchEvent(new CustomEvent('project:add-risk')); },
    add_issue(data) { window.dispatchEvent(new CustomEvent('project:add-issue')); },
    approve(data) { window.dispatchEvent(new CustomEvent('project:approve')); },
    close(data) { window.dispatchEvent(new CustomEvent('project:close')); },
    export(data) { window.open('/reports/export?type=project', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> new project */ },
  },
});

export default workspaceRegistry;
