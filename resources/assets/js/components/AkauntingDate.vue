<template>
    <base-input :label="title"
        :name="name"
        :class="[
            {'readonly': readonly},
            {'disabled': disabled},
            {'hidden-year': hiddenYear},
            {'data-value-min': dataValueMin},
            formClasses
        ]"
        :footer-error="formError"
        :appendIcon="icon"
        :readonly="readonly"
        :disabled="disabled"
        :not-required="notRequired"
        @focus="focus"
        >
        <flat-picker slot-scope="{focus, blur}"
            ref="flatpickr"
            :name="dataName"
            @on-open="onFlatpickrOpen(focus)"
            @on-close="onFlatpickrClose(blur)"
            :config="flatpickrConfig"
            class="datepicker w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
            :modelValue="real_model"
            @update:modelValue="onModelValueUpdate"
            :placeholder="placeholder"
            @on-change="change"
            :readonly="readonly"
            :disabled="disabled">
        </flat-picker>
    </base-input>
</template>

<script>
import flatPicker from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";

export default {
    name: 'akaunting-date',

    components: {
        flatPicker
    },

    props: {
        title: {
            type: String,
            default: '',
            description: "Modal header title"
        },

        dataName: {
            type: String,
            default: '',
            description: "Modal header title"
        },

        placeholder: {
            type: String,
            default: '',
            description: "Modal header title"
        },

        readonly: {
            type: Boolean,
            default: false,
            description: "Input readonly status"
        },

        notRequired: {
            type: Boolean,
            default: false
        },

        period: {
            type: [Number, String],
            default: "0",
            description: "Payment period"
        },

        disabled: {
            type: Boolean,
            default: false,
            description: "Input disabled status"
        },

        formClasses: null,

        formError: null,

        name: null,

        value: {
            default: null,
            description: "Input value defalut"
        },

        model: {
            default: null,
            description: "Input model defalut"
        },

        dateConfig: {
            type: Object,
            default: function () {
                return {};
            },
            description: "FlatPckr date configuration"
        },

        icon: {
            type: String,
            description: "Prepend icon (left)"
        },

        locale: {
            type: String,
            default: 'en',
        },

        hiddenYear: {
            type: [Boolean, String]
        },

        dataValueMin: {
            type: [Boolean, String, Date]
        }
    },

    data() {
        return {
            real_model: '',
        }
    },

    computed: {
        flatpickrConfig() {
            return {
                allowInput: true,
                altFormat: "d M Y",
                altInput: true,
                dateFormat: "Y-m-d",
                ...this.dateConfig
            };
        }
    },

    created() {
        if (this.locale !== 'en') {
            try {
                const lang = require(`flatpickr/dist/l10n/${this.locale}.js`).default[this.locale];

                this.dateConfig.locale = lang;
            }
            catch (e) {
            }
        }
    },

    mounted() {
        this.real_model = this.value;

        if (this.model) {
            this.real_model = this.model;
        }

        this.$emit('interface', this.real_model);

        // Ensure flatpickr instance is properly initialized
        this.$nextTick(() => {
            if (this.$refs.flatpickr && this.$refs.flatpickr.fp) {
                // Flatpickr instance is available and ready
            }
        });
    },

    methods: {
        onModelValueUpdate(value) {
            this.real_model = value;
        },

        change(selectedDates, dateStr) {
            // vue-flatpickr-component v5 passes (selectedDates, dateStr, instance)
            this.$emit('interface', dateStr);
            this.$emit('change', dateStr);
            this.$emit('input', dateStr);
        },

        onFlatpickrOpen(focusCallback) {
            if (typeof focusCallback === 'function') {
                focusCallback();
            }
            this.focus();
        },

        onFlatpickrClose(blurCallback) {
            if (typeof blurCallback === 'function') {
                blurCallback();
            }
        },

        focus() {
            let date_wrapper_html = document.querySelectorAll('.numInputWrapper');

            if (this.hiddenYear) {
                date_wrapper_html.forEach((wrapper) => {
                    wrapper.classList.add('hidden-year-flatpickr');
                });
            } else {
                date_wrapper_html.forEach((wrapper) => {
                    wrapper.classList.remove('hidden-year-flatpickr');
                });
            }
        },

        addDays(dateInput) {
            if (!this.period) {
                return;
            }

            let dateString = new Date(dateInput);
            let aMillisec = 86400000;
            let dateInMillisecs = dateString.getTime();
            let settingPaymentTermInMs = parseInt(this.period) * aMillisec;
            let prospectedDueDate = new Date(dateInMillisecs + settingPaymentTermInMs);

            return prospectedDueDate;
        },

        // Provide access to flatpickr instance for external components
        getFlatpickrInstance() {
            return this.$refs.flatpickr ? this.$refs.flatpickr.fp : null;
        },

        // Method to open the date picker programmatically
        open() {
            const fp = this.getFlatpickrInstance();
            if (fp && typeof fp.open === 'function') {
                fp.open();
            }
        },

        // Method to close the date picker programmatically
        close() {
            const fp = this.getFlatpickrInstance();
            if (fp && typeof fp.close === 'function') {
                fp.close();
            }
        },
    },

    watch: {
        value: function(val) {
            if (val !== this.real_model) {
                this.real_model = val;
            }
        },

        model: function(val) {
            if (val !== this.real_model) {
                this.real_model = val;
            }
        },

        dateConfig: function() {
           if (!this.dateConfig.minDate) {
               return;
           }

            if (this.real_model < this.dateConfig.minDate) {
                this.real_model = this.addDays(this.dateConfig.minDate);
            }
        },
    }
}
</script>

<style>
    .hidden-year-flatpickr {
        display: none !important;
    }
</style>
