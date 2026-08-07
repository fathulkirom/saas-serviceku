import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import AIOverview from '@/Pages/AI/sections/Overview.vue';

workspaceRegistry.register('ai', {
  tabs: { overview: AIOverview },
  sidebarWidgets: {},
  inspectorSections: {},
  actionHandlers: {
    new_chat(data) { window.dispatchEvent(new CustomEvent('ai:new-chat')); },
    ask_insight(data) { window.dispatchEvent(new CustomEvent('ai:ask-insight', { detail: data })); },
    run_prediction(data) { window.dispatchEvent(new CustomEvent('ai:run-prediction', { detail: data })); },
    get_recommendation(data) { window.dispatchEvent(new CustomEvent('ai:get-recommendation', { detail: data })); },
    save_prompt(data) { window.dispatchEvent(new CustomEvent('ai:save-prompt')); },
    daily_briefing(data) { window.dispatchEvent(new CustomEvent('ai:daily-briefing')); },
    executive_summary(data) { window.dispatchEvent(new CustomEvent('ai:executive-summary')); },
    export(data) { window.open('/reports/export?type=ai', '_blank'); },
  },
  shortcutHandlers: {
    refresh() { window.location.reload(); },
    chat() { /* Ctrl+Space -> open AI chat */ },
  },
});

export default workspaceRegistry;
