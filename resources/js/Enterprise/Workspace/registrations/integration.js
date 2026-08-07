import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import IntegrationOverview from '@/Pages/Integration/sections/Overview.vue';

workspaceRegistry.register('integration', {
  tabs: { overview: IntegrationOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    create_api_key(data) { window.dispatchEvent(new CustomEvent('integration:create-api-key')); },
    register_webhook(data) { window.dispatchEvent(new CustomEvent('integration:register-webhook')); },
    add_connector(data) { window.dispatchEvent(new CustomEvent('integration:add-connector')); },
    test_connection(data) { window.dispatchEvent(new CustomEvent('integration:test-connection', { detail: data })); },
    view_logs(data) { window.dispatchEvent(new CustomEvent('integration:view-logs')); },
    rotate_secret(data) { window.dispatchEvent(new CustomEvent('integration:rotate-secret')); },
    generate_swagger(data) { window.dispatchEvent(new CustomEvent('integration:generate-swagger')); },
    export(data) { window.open('/reports/export?type=integration', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
  },
});

export default workspaceRegistry;
