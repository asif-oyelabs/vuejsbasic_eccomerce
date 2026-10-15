
<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    editingId: {
        type: [Number, String],
        default: null,
    },
    form: {
        type: Object,
        required: true,
    },
    errorMessage: {
        type: String,
        default: '',
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit']);

const formElement = ref(null);

watch(
    () => props.show,
    (isVisible) => {
        if (!isVisible && formElement.value) {
            formElement.value.reset();
        }
    }
);
</script>

<template>
    <template v-if="show">
        <!-- Modal -->
        <div
            class="modal d-block"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="enquiryModalTitle"
            @click.self="emit('close')"
        >
            <div
                class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"
                role="document"
            >
                <div class="modal-content border-0 rounded-4 shadow">

                    <!-- Header -->
                    <div class="modal-header border-app px-4 py-3">
                        <div>
                            <h5
                                id="enquiryModalTitle"
                                class="modal-title fw-bold"
                            >
                                {{ editingId ? 'Edit Enquiry' : 'Add Enquiry' }}
                            </h5>

                            <p class="small text-app-muted mb-0 mt-1">
                                {{
                                    editingId
                                        ? 'Update the enquiry details below.'
                                        : 'Enter the enquiry details below.'
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Close"
                            :disabled="saving"
                            @click="emit('close')"
                        ></button>
                    </div>

                    <!-- Form -->
                    <form
                        ref="formElement"
                        @submit.prevent="emit('submit')"
                    >
                        <div class="modal-body p-4">

                            <!-- Error Message -->
                            <div
                                v-if="errorMessage"
                                class="alert alert-danger"
                                role="alert"
                            >
                                {{ errorMessage }}
                            </div>

                            <div class="row g-3">

                                <!-- Name -->
                                <div class="col-md-6">
                                    <label
                                        for="enquiryName"
                                        class="form-label fw-semibold"
                                    >
                                        Name
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        id="enquiryName"
                                        v-model="form.name"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter full name"
                                        maxlength="255"
                                        autocomplete="name"
                                        required
                                    />
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label
                                        for="enquiryEmail"
                                        class="form-label fw-semibold"
                                    >
                                        Email
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        id="enquiryEmail"
                                        v-model="form.email"
                                        type="email"
                                        class="form-control"
                                        placeholder="Enter email address"
                                        maxlength="255"
                                        autocomplete="email"
                                        required
                                    />
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label
                                        for="enquiryPhone"
                                        class="form-label fw-semibold"
                                    >
                                        Phone
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        id="enquiryPhone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="form-control"
                                        placeholder="Enter phone number"
                                        maxlength="20"
                                        autocomplete="tel"
                                        required
                                    />
                                </div>

                                <!-- Subject -->
                                <div class="col-md-6">
                                    <label
                                        for="enquirySubject"
                                        class="form-label fw-semibold"
                                    >
                                        Subject
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        id="enquirySubject"
                                        v-model="form.subject"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter subject"
                                        maxlength="255"
                                        required
                                    />
                                </div>

                                <!-- Message -->
                                <div class="col-12">
                                    <label
                                        for="enquiryMessage"
                                        class="form-label fw-semibold"
                                    >
                                        Message
                                        <span class="text-danger">*</span>
                                    </label>

                                    <textarea
                                        id="enquiryMessage"
                                        v-model="form.message"
                                        class="form-control"
                                        rows="5"
                                        maxlength="5000"
                                        placeholder="Enter enquiry message"
                                        required
                                    ></textarea>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6">
                                    <label
                                        for="enquiryStatus"
                                        class="form-label fw-semibold"
                                    >
                                        Status
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        id="enquiryStatus"
                                        v-model="form.status"
                                        class="form-select"
                                        required
                                    >
                                        <option value="" disabled>
                                            Select status
                                        </option>

                                        <option value="pending">
                                            Pending
                                        </option>

                                        <option value="read">
                                            Read
                                        </option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="modal-footer border-app px-4 py-3"
                        >
                            <button
                                type="button"
                                class="btn btn-outline-secondary px-4"
                                :disabled="saving"
                                @click="emit('close')"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="btn btn-success px-4"
                                :disabled="saving"
                            >
                                <span
                                    v-if="saving"
                                    class="spinner-border spinner-border-sm me-2"
                                    aria-hidden="true"
                                ></span>

                                {{
                                    saving
                                        ? 'Saving...'
                                        : editingId
                                            ? 'Update Enquiry'
                                            : 'Save Enquiry'
                                }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Backdrop -->
        <div class="modal-backdrop show"></div>
    </template>
</template>
