
<template>
    <div v-if="show" class="product-modal-backdrop" @click.self="close">
        <div
            class="product-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="productModalTitle"
        >
            <div class="modal-header px-4 py-3">
                <div>
                    <h5 id="productModalTitle" class="modal-title fw-bold">
                        {{ isEditing ? 'Edit Product' : 'Add Product' }}
                    </h5>
                    <p class="text-muted small mb-0">
                        Enter product information and upload images.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    :disabled="saving"
                    @click="close"
                ></button>
            </div>

            <form @submit.prevent="submitForm">
                <div class="modal-body p-4">
                    <div v-if="formError" class="alert alert-danger py-2">
                        {{ formError }}
                    </div>

                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model.trim="form.name"
                                type="text"
                                class="form-control"
                                maxlength="200"
                                required
                                placeholder="Enter product name"
                                @input="handleNameInput"
                            >
                            <small v-if="errors.name" class="text-danger">
                                {{ errors.name }}
                            </small>
                        </div>

                        <!-- Slug -->
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input
                                v-model.trim="form.slug"
                                type="text"
                                class="form-control"
                                maxlength="220"
                                placeholder="product-slug"
                                @input="slugManuallyChanged = true"
                            >
                            <small class="text-muted">
                                Generated automatically from the product name.
                            </small>
                            <small v-if="errors.slug" class="text-danger d-block">
                                {{ errors.slug }}
                            </small>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select
                                v-model="form.category_id"
                                class="form-select"
                                required
                            >
                                <option value="">Select category</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <small v-if="errors.category_id" class="text-danger">
                                {{ errors.category_id }}
                            </small>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select v-model="form.status" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <!-- Original price -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Original Price (₹) <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.original_price"
                                type="number"
                                class="form-control"
                                min="0"
                                step="0.01"
                                required
                                placeholder="0.00"
                            >
                            <small v-if="errors.original_price" class="text-danger">
                                {{ errors.original_price }}
                            </small>
                        </div>

                        <!-- Selling price -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Selling Price (₹) <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.price"
                                type="number"
                                class="form-control"
                                min="0"
                                step="0.01"
                                required
                                placeholder="0.00"
                            >
                            <small v-if="errors.price" class="text-danger">
                                {{ errors.price }}
                            </small>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Quantity <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.qty"
                                type="number"
                                class="form-control"
                                min="0"
                                step="1"
                                required
                                placeholder="0"
                            >
                            <small v-if="errors.qty" class="text-danger">
                                {{ errors.qty }}
                            </small>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea
                                v-model="form.description"
                                class="form-control"
                                rows="3"
                                placeholder="Enter product description"
                            ></textarea>
                        </div>

                        <!-- Main image -->
                        <div class="col-12">
                            <label class="form-label">Main Product Image</label>
                            <input
                                ref="mainImageInput"
                                type="file"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                @change="handleMainImage"
                            >
                            <small class="text-muted">
                                JPG, PNG or WebP; maximum 5 MB.
                            </small>

                            <div v-if="mainImagePreview" class="preview-card mt-3">
                                <img
                                    :src="mainImagePreview"
                                    alt="Main product preview"
                                    class="main-image-preview"
                                >

                                <div class="flex-grow-1">
                                    <div class="fw-semibold small">
                                        {{ mainImageFile ? 'New image selected' : 'Current image' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ mainImageFile?.name || 'Product main image' }}
                                    </small>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    @click="removeMainImage"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        <!-- Existing gallery images -->
                        <div v-if="isEditing && existingImages.length" class="col-12">
                            <label class="form-label">Current Gallery Images</label>

                            <div class="gallery-grid">
                                <div
                                    v-for="image in existingImages"
                                    :key="image.id"
                                    class="gallery-item"
                                    :class="{
                                        'gallery-item-removed': removedImageIds.includes(image.id)
                                    }"
                                >
                                    <img :src="image.image_url" alt="Gallery image">

                                    <label class="gallery-remove">
                                        <input
                                            type="checkbox"
                                            class="form-check-input mt-0"
                                            :checked="removedImageIds.includes(image.id)"
                                            @change="toggleRemoveImage(image.id, $event.target.checked)"
                                        >
                                        <span>
                                            {{ removedImageIds.includes(image.id) ? 'Will remove' : 'Remove' }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- New gallery images -->
                        <div class="col-12">
                            <label class="form-label">Gallery Images</label>
                            <input
                                ref="galleryInput"
                                type="file"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                                @change="handleGalleryImages"
                            >
                            <small class="text-muted">
                                Select multiple images. Maximum 10 new images per save,
                                each up to 5 MB.
                            </small>

                            <div v-if="galleryFiles.length" class="gallery-grid mt-3">
                                <div
                                    v-for="(item, index) in galleryFiles"
                                    :key="item.key"
                                    class="gallery-item"
                                >
                                    <img :src="item.preview" :alt="item.file.name">

                                    <button
                                        type="button"
                                        class="gallery-delete-button"
                                        title="Remove selected image"
                                        @click="removeGalleryFile(index)"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>

                                    <small class="gallery-filename">
                                        {{ item.file.name }}
                                    </small>
                                </div>
                            </div>

                            <small v-if="errors.images" class="text-danger d-block mt-1">
                                {{ errors.images }}
                            </small>
                            <small v-if="clientImageError" class="text-danger d-block mt-1">
                                {{ clientImageError }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-4 py-3">
                    <button
                        type="button"
                        style="margin-right: 2px;"
                        class="btn btn-danger"
                        :disabled="saving"
                        @click="close"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success" :disabled="saving">
                        <span
                            v-if="saving"
                            class="spinner-border spinner-border-sm me-2"
                        ></span>
                        <i v-else class="bi bi-check-lg me-1"></i>
                        {{ saving ? 'Saving...' : (isEditing ? 'Update Product' : 'Save Product') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    product: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit']);

const emptyForm = () => ({
    name: '',
    slug: '',
    category_id: '',
    description: '',
    original_price: '',
    price: '',
    qty: 0,
    status: '1',
});

const form = reactive(emptyForm());
const errors = reactive({});
const existingImages = ref([]);
const removedImageIds = ref([]);
const galleryFiles = ref([]);
const mainImageFile = ref(null);
const mainImagePreview = ref('');
const removeCurrentMainImage = ref(false);
const slugManuallyChanged = ref(false);
const formError = ref('');
const clientImageError = ref('');

const mainImageInput = ref(null);
const galleryInput = ref(null);

const isEditing = computed(() => Boolean(props.product?.id));

let previewKey = 0;

function clearErrors() {
    Object.keys(errors).forEach(key => delete errors[key]);
    formError.value = '';
    clientImageError.value = '';
}

function releaseGalleryPreviews() {
    galleryFiles.value.forEach(item => URL.revokeObjectURL(item.preview));
    galleryFiles.value = [];
}

function resetForm() {
    Object.assign(form, emptyForm());
    clearErrors();

    existingImages.value = [];
    removedImageIds.value = [];
    releaseGalleryPreviews();

    mainImageFile.value = null;
    mainImagePreview.value = '';
    removeCurrentMainImage.value = false;
    slugManuallyChanged.value = false;

    if (mainImageInput.value) mainImageInput.value.value = '';
    if (galleryInput.value) galleryInput.value.value = '';
}

function populateForm(product) {
    resetForm();

    if (!product) return;

    Object.assign(form, {
        name: product.name ?? '',
        slug: product.slug ?? '',
        category_id: String(product.category_id ?? ''),
        description: product.description ?? '',
        original_price: product.original_price ?? '',
        price: product.price ?? '',
        qty: product.qty ?? 0,
        status: product.status ? '1' : '0',
    });

    mainImagePreview.value = product.main_image_url ?? '';
    existingImages.value = [...(product.images ?? [])];
}

function slugify(value) {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function handleNameInput() {
    if (!slugManuallyChanged.value) {
        form.slug = slugify(form.name);
    }
}

function handleMainImage(event) {
    clientImageError.value = '';

    const file = event.target.files?.[0];
    if (!file) return;

    if (!validateImage(file)) {
        event.target.value = '';
        return;
    }

    mainImageFile.value = file;
    mainImagePreview.value = URL.createObjectURL(file);
    removeCurrentMainImage.value = false;
}

function removeMainImage() {
    mainImageFile.value = null;
    mainImagePreview.value = '';
    removeCurrentMainImage.value = true;

    if (mainImageInput.value) mainImageInput.value.value = '';
}

function handleGalleryImages(event) {
    clientImageError.value = '';

    const selectedFiles = Array.from(event.target.files ?? []);

    if (galleryFiles.value.length + selectedFiles.length > 10) {
        clientImageError.value = 'You can upload a maximum of 10 new gallery images per save.';
        event.target.value = '';
        return;
    }

    for (const file of selectedFiles) {
        if (!validateImage(file)) {
            event.target.value = '';
            return;
        }
    }

    selectedFiles.forEach(file => {
        galleryFiles.value.push({
            key: ++previewKey,
            file,
            preview: URL.createObjectURL(file),
        });
    });

    // Clear input so the same file can be selected again if needed.
    event.target.value = '';
}

function validateImage(file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        clientImageError.value = 'Only JPG, PNG and WebP images are allowed.';
        return false;
    }

    if (file.size > 5 * 1024 * 1024) {
        clientImageError.value = `${file.name} exceeds the 5 MB limit.`;
        return false;
    }

    return true;
}

function removeGalleryFile(index) {
    URL.revokeObjectURL(galleryFiles.value[index].preview);
    galleryFiles.value.splice(index, 1);
    clientImageError.value = '';
}

function toggleRemoveImage(id, checked) {
    if (checked && !removedImageIds.value.includes(id)) {
        removedImageIds.value.push(id);
    } else if (!checked) {
        removedImageIds.value = removedImageIds.value.filter(imageId => imageId !== id);
    }
}

function close() {
    if (!props.saving) {
        emit('close');
    }
}

function submitForm() {
    clearErrors();

    if (!form.name || !form.category_id) {
        formError.value = 'Please enter the product name and select a category.';
        return;
    }

    if (clientImageError.value) return;

    const formData = new FormData();

    formData.append('name', form.name);
    formData.append('slug', form.slug);
    formData.append('category_id', form.category_id);
    formData.append('description', form.description);
    formData.append('original_price', form.original_price);
    formData.append('price', form.price);
    formData.append('qty', form.qty);
    formData.append('status', form.status);

    if (mainImageFile.value) {
        formData.append('main_image', mainImageFile.value);
    }

    if (isEditing.value && removeCurrentMainImage.value && !mainImageFile.value) {
        formData.append('remove_main_image', '1');
    }

    galleryFiles.value.forEach(item => {
        formData.append('images[]', item.file);
    });

    removedImageIds.value.forEach(id => {
        formData.append('remove_image_ids[]', id);
    });

    emit('submit', formData);
}

watch(
    () => [props.show, props.product],
    ([show, product]) => {
        if (show) {
            populateForm(product);
        } else {
            resetForm();
        }
    },
    { immediate: true }
);

watch(
    () => props.show,
    show => {
        if (show) {
            document.body.classList.add('product-modal-open');
        } else {
            document.body.classList.remove('product-modal-open');
        }
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    releaseGalleryPreviews();
    document.body.classList.remove('product-modal-open');
});
</script>

<style scoped>

.product-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1055;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    overflow: hidden;
    background: rgb(15 23 42 / 55%);
}

.product-modal {
    width: min(900px, 100%);
    height: min(850px, calc(100vh - 24px));
    max-height: calc(100vh - 24px);
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    margin: 0;
    background: var(--app-white);
    border: 1px solid var(--app-border);
    border-radius: 14px;
    box-shadow: 0 20px 60px rgb(0 0 0 / 20%);
}

.product-modal > form {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.product-modal .modal-header,
.product-modal .modal-footer {
    flex: 0 0 auto;
    background: var(--app-white);
}

.product-modal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}

:global(body.product-modal-open) {
    overflow: hidden;
}

@media (max-width: 576px) {
    .product-modal-backdrop {
        padding: 8px;
    }

    .product-modal {
        width: 100%;
        height: calc(100vh - 16px);
        max-height: calc(100vh - 16px);
        border-radius: 10px;
    }

    .product-modal .modal-body {
        padding: 16px !important;
    }
}
</style>
