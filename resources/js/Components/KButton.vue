<template>
    <component
        :is="tag"
        v-bind="linkAttrs"
        :type="isButton ? type : undefined"
        :disabled="isButton ? disabled : undefined"
        :class="classes"
        :style="buttonStyle"
    >
        <slot />
    </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/**
 * Tombol reusable yang mereplikasi persis kelas/style tombol yang sebelumnya
 * diduplikasi inline di banyak halaman (terutama halaman detail Servis).
 * Tidak mengubah tampilan — hanya memusatkan definisi kelas/style.
 *
 * Variant:
 *  - action-indigo / action-info / action-success / action-warning / action-danger / action-blue / action-outline
 *  - modal-secondary / modal-primary-indigo / modal-primary-danger / modal-primary-success / modal-primary
 *  - text-danger / text-link
 * size:
 *  - sm (px-3 py-1.5 rounded-lg text-xs) -> aksi di action bar
 *  - md (px-4 py-2 rounded-lg text-xs)   -> upload
 */
const props = defineProps({
    variant: { type: String, default: '' }, // '' = passthrough (gunakan class/style parent)
    size: { type: String, default: '' },
    shadow: { type: Boolean, default: false },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
    to: { type: String, default: '' },
    href: { type: String, default: '' },
    target: { type: String, default: '' },
    extraClass: { type: String, default: '' },
    buttonStyle: { type: [Object, String], default: null },
});

const isButton = computed(() => !props.to && !props.href);

const tag = computed(() => {
    if (props.to) return Link;
    if (props.href) return 'a';
    return 'button';
});

const linkAttrs = computed(() => {
    if (props.to) return { href: props.to };
    if (props.href) return { href: props.href, target: props.target || undefined };
    return {};
});

const variantMap = {
    '': { cls: '', style: '' },
    // Standard .btn* dari app.css
    'primary': { cls: 'btn btn-primary', style: '' },
    'secondary': { cls: 'btn btn-secondary', style: '' },
    'danger': { cls: 'btn btn-danger', style: '' },
    'success': { cls: 'btn btn-success', style: '' },
    // Action bar (detail Servis)
    'action-indigo': { cls: 'text-white bg-indigo-600', style: '' },
    'action-info': { cls: 'text-white', style: 'background: var(--info);' },
    'action-success': { cls: 'text-white', style: 'background: var(--success);' },
    'action-warning': { cls: 'text-white', style: 'background: var(--warning);' },
    'action-danger': { cls: 'text-white', style: 'background: var(--danger);' },
    'action-blue': { cls: 'text-white', style: 'background: #2563eb;' },
    'action-outline': { cls: '', style: 'background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);' },
    'modal-secondary': { cls: 'flex-1 px-4 py-2 rounded-xl border text-sm font-semibold transition-all', style: 'border-color: var(--border-color); color: var(--text-secondary); background: var(--bg-hover);' },
    'modal-primary': { cls: 'flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50', style: '' },
    'modal-primary-indigo': { cls: 'flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50 bg-indigo-600', style: '' },
    'modal-primary-danger': { cls: 'flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50', style: 'background: var(--danger);' },
    'modal-primary-success': { cls: 'flex-1 px-4 py-2 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50', style: 'background: var(--success);' },
    'text-danger': { cls: 'text-xs', style: 'color: var(--danger);' },
    'text-link': { cls: 'text-xs font-semibold text-indigo-600', style: '' },
};

const isAction = computed(() => props.variant.startsWith('action-'));

const sizeClass = computed(() => {
    if (props.variant.startsWith('action-')) {
        if (props.size === 'md') return 'px-4 py-2 rounded-lg text-xs font-bold';
        return 'px-3 py-1.5 rounded-lg text-xs font-bold'; // default sm untuk action
    }
    if (props.size === 'xs') return 'btn btn-xs';
    if (props.size === 'lg') return 'btn btn-lg';
    return '';
});

const classes = computed(() => {
    const v = variantMap[props.variant] || variantMap[''];
    const list = [];
    if (isAction.value) {
        list.push('inline-flex items-center gap-1.5 transition-all hover:shadow-sm disabled:opacity-50');
        list.push(sizeClass.value);
        if (v.cls) list.push(v.cls);
    } else if (v.cls) {
        list.push(v.cls);
    }
    if (props.shadow) list.push('shadow-sm');
    if (props.extraClass) list.push(props.extraClass);
    return list;
});

const buttonStyle = computed(() => {
    const v = variantMap[props.variant] || variantMap[''];
    return props.buttonStyle || (v.style || undefined);
});
</script>
