import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import NotificationOverview from '@/Pages/Notification/sections/Overview.vue';

workspaceRegistry.register('notification', {
  tabs: { overview: NotificationOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    send_message(data) { window.dispatchEvent(new CustomEvent('notification:send-message')); },
    create_template(data) { window.dispatchEvent(new CustomEvent('notification:create-template')); },
    create_campaign(data) { window.dispatchEvent(new CustomEvent('notification:create-campaign')); },
    broadcast(data) { window.dispatchEvent(new CustomEvent('notification:broadcast')); },
    retry_failed(data) { window.dispatchEvent(new CustomEvent('notification:retry-failed')); },
    configure_channel(data) { window.dispatchEvent(new CustomEvent('notification:configure-channel')); },
    view_analytics(data) { window.dispatchEvent(new CustomEvent('notification:view-analytics')); },
    export(data) { window.open('/reports/export?type=notification', '_blank'); },
  },
  shortcutHandlers: { refresh() { window.location.reload(); } },
});

export default workspaceRegistry;
