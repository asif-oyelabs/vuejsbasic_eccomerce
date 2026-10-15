
<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    category: { type: Object, default: null },
    saving: { type: Boolean, default: false },
    errorMessage: { type: String, default: '' },
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    name: '',
    slug: '',
    sort_order: 0,
    status: true,
    image: null,
});

const errors = ref({});
const imagePreview = ref('');
const slugManuallyEdited = ref(false);

const isEditing = computed(() => !!props.category);

watch(
    () => [props.show, props.category],
    () => {
        if (!props.show) return;

        errors.value = {};
        slugManuallyEdited.value = false;

        form.value = {
            name: props.category?.name ?? '',
            slug: props.category?.slug ?? '',
            sort_order: props.category?.sort_order ?? 0,
            status: props.category
                ? Boolean(props.category.status)
                : true,
            image: null,
        };

        imagePreview.value = props.category?.image_url ?? '';
    },
    { immediate: true }
);

function makeSlug(value) {
    return value
        .toLowerCase()
        .trim()
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function onNameInput() {
    if (!slugManuallyEdited.value) {
        form.value.slug = makeSlug(form.value.name);
    }
}

function onSlugInput() {
    slugManuallyEdited.value = true;
    form.value.slug = makeSlug(form.value.slug);
}

function onImageChange(event) {
    const file = event.target.files?.[0];

    if (!file) return;

    errors.value.image = '';

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        errors.value.image = 'Choose a JPG, PNG, or WebP image.';
        event.target.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        errors.value.image = 'Image size must not exceed 2 MB.';
        event.target.value = '';
        return;
    }

    form.value.image = file;

    if (imagePreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(imagePreview.value);
    }

    imagePreview.value = URL.createObjectURL(file);
}

function closeModal() {
    if (props.saving) return;
    emit('close');
}

function submitForm() {
    errors.value = {};

    if (!form.value.name.trim()) {
        errors.value.name = 'Category name is required.';
    }

    if (!form.value.slug.trim()) {
        errors.value.slug = 'Slug is required.';
    }

    if (
        form.value.sort_order === '' ||
        Number(form.value.sort_order) < 0 ||
        !Number.isInteger(Number(form.value.sort_order))
    ) {
        errors.value.sort_order = 'Enter a valid non-negative integer.';
    }

    if (Object.keys(errors.value).length) return;

    emit('submit', {
        ...form.value,
        sort_order: Number(form.value.sort_order),
    });
}
</script>

<template>
    <div
        v-if="show"
        class="modal-backdrop-custom"
        @click.self="closeModal"
        @keydown.esc="closeModal"
    >
        <section
            class="category-modal card border-0 shadow-lg"
            role="dialog"
            aria-modal="true"
            aria-labelledby="categoryModalTitle"
        >
            <div class="modal-header-custom">
                <div>
                    <h5 id="categoryModalTitle" class="fw-bold mb-1">
                        {{ isEditing ? 'Edit Category' : 'Add Category' }}
                    </h5>
                    <p class="text-app-muted small mb-0">
                        Enter the category details below.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    :disabled="saving"
                    @click="closeModal"
                ></button>
            </div>

            <form @submit.prevent="submitForm">
                <div class="modal-body-custom">
                    <div
                        v-if="errorMessage"
                        class="alert alert-danger py-2"
                        role="alert"
                    >
                        {{ errorMessage }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.name }"
                            maxlength="150"
                            placeholder="e.g. Electronics"
                            @input="onNameInput"
                        />
                        <div v-if="errors.name" class="invalid-feedback">
                            {{ errors.name }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Slug <span class="text-danger">*</span>
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.slug }"
                            maxlength="180"
                            placeholder="electronics"
                            @input="onSlugInput"
                        />
                        <div v-if="errors.slug" class="invalid-feedback">
                            {{ errors.slug }}
                        </div>
                        <small class="text-app-muted">
                            Generated from the category name; editable.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Category Image
                        </label>

                        <div class="d-flex align-items-center gap-3">
                            <div class="image-preview">
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt="Category preview"
                                />
                                <i v-else class="bi bi-image text-app-muted fs-3"></i>
                            </div>

                            <div class="flex-grow-1">
                                <input
                                    type="file"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.image }"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    @change="onImageChange"
                                />
                                <div v-if="errors.image" class="text-danger small mt-1">
                                    {{ errors.image }}
                                </div>
                                <small class="text-app-muted">
                                    JPG, PNG or WebP; maximum 2 MB.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">
                                Sort Order
                            </label>
                            <input
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                step="1"
                                class="form-control"
                                :class="{ 'is-invalid': errors.sort_order }"
                            />
                            <div v-if="errors.sort_order" class="invalid-feedback">
                                {{ errors.sort_order }}
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold d-block">
                                Status
                            </label>
                            <div class="form-check form-switch mt-2">
                                <input
                                    id="categoryStatus"
                                    v-model="form.status"
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                />
                                <label class="form-check-label" for="categoryStatus">
                                    {{ form.status ? 'Active' : 'Inactive' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button
                        type="button"
                        class="btn btn-light border"
                        :disabled="saving"
                        @click="closeModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-success"
                        :disabled="saving"
                    >
                        <span
                            v-if="saving"
                            class="spinner-border spinner-border-sm me-2"
                        ></span>
                        {{ saving ? 'Saving...' : (isEditing ? 'Update Category' : 'Save Category') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</template>

<style scoped>
.modal-backdrop-custom {
    position: fixed;
    inset: 0;
    z-index: 1055;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 20px;
    background: rgba(15, 23, 42, 0.55);
}

.category-modal {
    width: 100%;
    max-width: 650px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    border-radius: 16px;
}

.modal-header-custom,
.modal-footer-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 24px;
}

.modal-header-custom {
    border-bottom: 1px solid var(--app-border);
}

.modal-body-custom {
    padding: 24px;
}

.modal-footer-custom {
    justify-content: flex-end;
    border-top: 1px solid var(--app-border);
    background: var(--app-bg);
    border-radius: 0 0 16px 16px;
}

.text-app-muted {
    color: var(--app-muted);
}

.image-preview {
    display: grid;
    place-items: center;
    width: 84px;
    height: 84px;
    flex: 0 0 84px;
    overflow: hidden;
    border: 1px solid var(--app-border);
    border-radius: 12px;
    background: var(--app-bg);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

@media (max-width: 575.98px) {
    .modal-backdrop-custom {
        padding: 10px;
    }

    .modal-header-custom,
    .modal-body-custom,
    .modal-footer-custom {
        padding: 16px;
    }
}
</style>
