<template>
    <div class="relative w-full h-full flex">
        <slot name="search">
            <input
                v-if="! show_date"
                type="text"
                class="w-full h-12 lg:h-auto bg-transparent text-black text-sm border-0 pb-0 focus:outline-none focus:ring-transparent focus:border-purple-100"
                :class="!show_icon ? 'ltr:pr-4 rtl:pl-4' : 'ltr:pr-10 rtl:pl-10'"
                :placeholder="dynamicPlaceholder"
                :ref="'input-search-field-' + id"
                v-model="search"
                @focus="onInputFocus"
                @input="onInput"
                @blur="onBlur"
                @keyup.enter="onInputConfirm"
            />
        </slot>

        <slot name="date">
            <flat-picker
                v-if="show_date"
                ref="flatpickrFilter"
                @on-open="onInputFocus"
                @blur="onBlur"
                :config="dateConfig"
                class="w-full h-12 lg:h-auto bg-transparent text-black text-sm border-0 pb-0 focus:outline-none focus:ring-transparent focus:border-purple-100 datepicker"
                :class="!show_icon ? 'ltr:pr-4 rtl:pl-4' : 'ltr:pr-10 rtl:pl-10'"
                :placeholder="dynamicPlaceholder"
                :ref="'input-search-date-field-' + id"
                :modelValue="''"
                :value="''"
                @focus="onInputFocus"
                @on-close="onInputDateSelected"
                @keyup.enter="onInputConfirm"
            >
            </flat-picker>
        </slot>

        <button
            v-if="show_icon"
            @focus="onInputFocus"
            v-show="show_button"
            @click="onInputConfirm"
            class="absolute ltr:right-0.5 rtl:left-0.5 z-50 mt-3 text-sm text-gray-700 font-medium px-2.5 py-1 h-7 rounded-lg"
            :class="search.length == 0 ? 'opacity-60 cursor-default' : 'cursor-pointer hover:bg-gray-100'"
            :disabled="search.length == 0"
        >
            <div class="flex">
                <span>search</span>
                <span class="material-icons-outlined text-sm ltr:scale-x-100 rtl:-scale-x-100 ltr:ml-1 rtl:mr-1 mt-0.5">
                    keyboard_return
                </span>
            </div>
        </button>

        <button type="button" class="absolute ltr:right-0 rtl:left-0 top-4 lg:top-2 clear" v-if="show_close_icon" @click="onSearchAndFilterClear">
            <span class="material-icons text-sm">close</span>
        </button>

        <filters
            :id="id"
            :filters="filters"
            :search="search"

            :search-text="searchText"
        ></filters>
    </div>
</template>

<script>
    import {getQueryVariable} from './../../plugins/functions';
    import Filters from "./Filters";
    import flatPicker from "vue-flatpickr-component";
    import "flatpickr/dist/flatpickr.css";

    export default {
        name: 'filter-main',

        components: {
            flatPicker,
            Filters,
        },

        props: {
            id: {
                type: Number,
                default: 0,
                description: '',
            },
            
            placeholder: {
                type: String,
                default: 'Search or filter results...',
                description: 'Input placeholder'
            },

            filters: {
                type: Array|Object,
                default: [],
                description: 'Get Filter List'
            },

            searchText: {
                type: String,
                default: 'Search for this text',
                description: 'Input placeholder'
            },
        },

        data() {
            return {
                search: '', // search cloumn model

                show_date: false,
                show_button: false,
                show_close_icon: false,
                show_icon: true,
                input_focus: false,
                defaultPlaceholder: this.placeholder,
                dynamicPlaceholder: this.placeholder,
                
                // Date picker configuration
                dateConfig: {
                    allowInput: true,
                    altFormat: "d M Y",
                    altInput: true,
                    dateFormat: "Y-m-d",
                },
            };
        },

        methods: {
            onInputFocus() {
                this.show_button = true;

                // For date picker, show it directly
                if (this.show_date) {
                    this.$nextTick(() => {
                        const flatpickrRef = this.$refs['input-search-date-field-' + this.id];
                        if (flatpickrRef && flatpickrRef.fp && typeof flatpickrRef.fp.open === 'function') {
                            flatpickrRef.fp.open();
                        }
                    });
                }

                this.input_focus = true;
            },

            onBlur() {
                this.input_focus = false;
            },

            onInput(evt) {
                this.search = evt.target.value;
                this.show_button = true;
                this.$emit('input', evt.target.value);
            },

            onInputDateSelected() {
                // Handle date selection
                this.show_button = true;
            },

            onSearchAndFilterClear() {
                this.search = '';
                this.show_date = false;
                this.show_button = false;
                this.show_close_icon = false;
                this.dynamicPlaceholder = this.defaultPlaceholder;
            },

            onInputConfirm() {
                if (!this.search) {
                    return;
                }

                // Simple search functionality
                let path = window.location.href.replace(window.location.search, '');
                let args = '?search="' + this.search + '"';
                
                window.location = path + args;
            },
        },

        created() {

        },

        mounted() {

        },

        computed: {

        },

        watch: {

        },
    };
</script>

<style>
    .searh-field .form-control.datepicker.flatpickr-input {
        padding: inherit !important;
    }
</style>
