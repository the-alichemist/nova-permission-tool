<template>
    <default-field :field="field" class="w-4/5">
        <template v-slot:field class="w-4/5">
            <div class="flex flex-wrap content-start " >
                <div v-for="group in availableGroups" :key="group.name" class="flex items-center w-1/3 mb-2">
                    <fieldset class="w-full p-2 m-1 h-full">
                        <legend class="mx-1 px-1">
                            <div class="flex">
                                <checkbox
                                    class=""
                                    @input="selectAllGroupedOptions(group.options)"
                                    :id="group.name"
                                    :name="group.name"
                                    :checked="isAllGroupedOptionsSelected(group.options)"
                                >&nbsp;</checkbox>
                                <h4> {{group.name}}</h4>
                            </div>

                        </legend>

                        <div v-for="option in group.options" :key="option.value" @click="handleChange(option.value)" class="flex  mb-1">
                            <div>
                                <checkbox
                                class="py-2 mr-4"
                                :id="field.name"
                                :name="field.name"
                                :checked="options[option.value]"
                                ></checkbox>
                                <label
                                :for="field.name"
                                v-text="option.display"
                                ></label>
                            </div>
                        </div>
                        <div v-if="group.actions.length > 0">
                            <h4>Actions</h4>
                            <hr class="border-t">

                            <div v-for="option in group.actions" :key="option.value" @click="handleChange(option.value)" class="flex mb-1">
                                <div>
                                    <checkbox
                                    class="py-2 mr-4"
                                    :id="field.name"
                                    :name="field.name"
                                    :checked="options[option.value]"
                                    ></checkbox>
                                    <label
                                    :for="field.name"
                                    v-text="option.display"
                                    ></label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors, Minimum } from 'laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data: () => ({
        options: []
    }),

    computed: {
        availableGroups() {
            var groups = {}
            this.field.options.forEach(option => {
                if (!groups[option.resource]) {
                    groups[option.resource] = {name: option.resource, options: [], actions: []};
                }
                if (option.action) {
                    groups[option.resource].actions.push(option);
                } else {
                    groups[option.resource].options.push(option);
                }
            })
            console.log(groups);
            return _.toArray(groups)

        },
    },
    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || '';
            this.$nextTick(() => {
                this.options = (this.value)
                    ? JSON.parse(this.value)
                    : [];
            });
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(key) {
            this.options[key] = ! this.options[key];
        },

        isAllGroupedOptionsSelected(options){
            let _this = this;
            let result = true;
            _.each(options, function(option) {
                if(_this.options[option.value] == false) {
                    result = false;
                    return false;
                }
            });
            return result;
        },
        selectAllGroupedOptions(options) {
            let _this = this;
            if(this.isAllGroupedOptionsSelected(options)) {
                _.each(options, function(option) {
                    _this.options[option.value] = false;
                });
                return;
            }
            _.each(options, function(option) {
                _this.options[option.value] = true;
            });
        },
    },
    watch: {
        'options' : {
            handler: function (newOptions) {
                this.value = JSON.stringify(newOptions);
            },
            deep: true
        }
    }
}
</script>

<style>
    fieldset {
        border: 1px groove;
    }
</style>
