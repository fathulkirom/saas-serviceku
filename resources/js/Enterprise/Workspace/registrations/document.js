import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import DocumentOverview from '@/Pages/Document/sections/Overview.vue';

workspaceRegistry.register('document', {
  tabs: { overview: DocumentOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    upload(data) { window.dispatchEvent(new CustomEvent('document:upload')); },
    new_version(data) { window.dispatchEvent(new CustomEvent('document:new-version')); },
    request_approval(data) { window.dispatchEvent(new CustomEvent('document:request-approval')); },
    share(data) { window.dispatchEvent(new CustomEvent('document:share')); },
    add_comment(data) { window.dispatchEvent(new CustomEvent('document:add-comment')); },
    run_ocr(data) { window.dispatchEvent(new CustomEvent('document:run-ocr')); },
    publish_knowledge(data) { window.dispatchEvent(new CustomEvent('document:publish-knowledge')); },
    export(data) { window.open('/reports/export?type=document', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    new() { /* Ctrl+N -> upload document */ },
  },
});

export default workspaceRegistry;
