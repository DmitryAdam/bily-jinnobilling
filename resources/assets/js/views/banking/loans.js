/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./../../bootstrap');

import Vue from 'vue';

import DashboardPlugin from './../../plugins/dashboard-plugin';

import Global from './../../mixins/global';

import Form from './../../plugins/form';
import BulkAction from './../../plugins/bulk-action';

// plugin setup
Vue.use(DashboardPlugin);

const app = new Vue({
    el: '#main-body',

    mixins: [
        Global
    ],

    data: function () {
        // Check which form exists on the page
        let formId = 'loan';

        if (document.getElementById('loan-payment')) {
            formId = 'loan-payment';
        }

        return {
            form: new Form(formId),
            bulk_action: new BulkAction('loans'),
            payment_modal: false,
        }
    },

    watch: {
        payment_modal(val) {
            if (val) {
                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => {
                    if (document.getElementById('loan-payment')) {
                        this.form = new Form('loan-payment');
                    }
                });
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        },
    },
});
