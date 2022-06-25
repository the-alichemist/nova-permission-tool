<template>
  <default-field :field="field" class="w-full">
    <template v-slot:field class="w-full">
      <h2 class="text-xl my-11 ml-2">CRUD Permissions</h2>
      <table class="table-fixed">
        <thead class="bg-gray-100">
          <tr>
            <th
              v-for="resourceColumn in resourceColumns"
              :key="resourceColumn"
              scope="col"
              class="text-sm font-medium px-4 py-4"
            >
              {{ resourceColumn }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr class="" v-for="group in availableGroups" :key="group.name">
            <td class="flex items-center py-1 pl-5">
              <checkbox
                class="mr-1"
                @input="selectAllGroupedOptions(group.options)"
                :name="group.name"
                :checked="isAllGroupedOptionsSelected(group.options)"
                >&nbsp;</checkbox
              >
              <h2 class="text-base">{{ group.name }}</h2>
            </td>

            <td class="" v-for="option in group.options" :key="option.value">
              <div class="flex items-center">
                <checkbox
                  class="py-2 m-auto"
                  @click="handleChange(option.value)"
                  :name="field.name"
                  :checked="options[option.value]"
                ></checkbox>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="flex">
        <div class="w-1/2">
          <h2 class="text-xl my-11 ml-2">Action and Field Permissions</h2>
          <div v-for="group in availableGroups" :key="group.name">
            <accordion>
              <template #title>
                {{ group.name }}
              </template>

              <template #toggleable>
                <div class="ml-5 mb-7 my-2" v-if="group.actions.length > 0">
                  <!-- <h4 class="text-lg my-2">Actions</h4> -->
                  <div class="flex items-center">
                    <div
                      v-for="option in group.actions"
                      :key="option.value"
                      class="flex mb-1 mr-10"
                    >
                      <div>
                        <checkbox
                          @click="handleChange(option.value)"
                          class="py-2 mr-1"
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
                </div>

                <table class="table-fixed min-w-full w-40">
                  <thead class="bg-gray-100">
                    <tr>
                      <th
                        v-for="fieldColumn in fieldColumns"
                        :key="fieldColumn"
                        scope="col"
                        class="text-sm font-medium py-4"
                      >
                        {{ fieldColumn }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(option, fieldName) in group.fields"
                      :key="fieldName"
                      class="bg-white border-b"
                    >
                      <td class="text-base py-1.5 pl-5">
                        {{ fieldName }}
                      </td>
                      <td>
                        <div>
                          <checkbox
                            @click="handleChange(option.hidden.value)"
                            class="py-2 flex items-center m-auto"
                            :name="field.name"
                            :checked="options[option.hidden.value]"
                          ></checkbox>
                        </div>
                      </td>
                      <td>
                        <div>
                          <checkbox
                            @click="handleChange(option.readonly.value)"
                            class="py-2 flex items-center m-auto"
                            :name="field.name"
                            :checked="options[option.readonly.value]"
                          ></checkbox>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </template>
            </accordion>
          </div>
        </div>
        <div class="ml-14">
          <h2 class="text-xl my-11 ml-2">Tool Permissions</h2>
          <div class="ml-5 mb-7" v-if="tools.length > 0">
            <div class="flex items-center">
              <div
                v-for="option in tools"
                :key="option.value"
                class="flex mb-1 mr-10"
              >
                <div>
                  <checkbox
                    @click="handleChange(option.value)"
                    class="py-2 mr-1"
                    :name="field.name"
                    :checked="options[option.value]"
                  ></checkbox>
                  <label :for="field.name" v-text="option.display"></label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors, Minimum } from "laravel-nova";

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ["resourceName", "resourceId", "field"],

  data: () => ({
    options: [],
    fieldColumns: ["Fields", "Hidden", "Readonly"],
    resourceColumns: [
      "Resource",
      "Create",
      "View",
      "View Any",
      "Update",
      "Update Any",
      "Delete",
      "Delete Any",
      "Force Delete",
      "Restore",
      "Attach",
      "Detach",
    ],
    tools: [],
  }),

  computed: {
    availableGroups() {
      var groups = {};
      this.field.options.forEach((option) => {
        if (!groups[option.resource]) {
          groups[option.resource] = {
            name: option.resource,
            options: [],
            actions: [],
            fields: {},
          };
        }
        if (option.action) {
          groups[option.resource].actions.push(option);
        } else if (option.field) {
          let fieldNameExists = groups[option.resource].fields.hasOwnProperty(
            option.display
          );
          if (!fieldNameExists) {
            groups[option.resource].fields[option.display] = {
              hidden: "",
              readonly: "",
            };
          }
          groups[option.resource].fields[option.display][option.fieldType] =
            option;
        } else if (option.resource === "Tools") {
          this.tools.push(option);
          delete groups[option.resource];
        } else {
          groups[option.resource].options.push(option);
        }
      });

      return _.toArray(groups);
    },
  },
  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value || "";
      this.$nextTick(() => {
        this.options = this.value ? JSON.parse(this.value) : [];
      });
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      formData.append(this.field.attribute, this.value || "");
    },

    /**
     * Update the field's internal value.
     */
    handleChange(key) {
      this.options[key] = !this.options[key];
    },

    isAllGroupedOptionsSelected(options) {
      let _this = this;
      let result = true;
      _.each(options, function (option) {
        if (_this.options[option.value] == false) {
          result = false;
          return false;
        }
      });
      return result;
    },
    selectAllGroupedOptions(options) {
      let _this = this;
      if (this.isAllGroupedOptionsSelected(options)) {
        _.each(options, function (option) {
          _this.options[option.value] = false;
        });
        return;
      }
      _.each(options, function (option) {
        _this.options[option.value] = true;
      });
    },
  },
  watch: {
    options: {
      handler: function (newOptions) {
        this.value = JSON.stringify(newOptions);
      },
      deep: true,
    },
  },
};
</script>

<style>
fieldset {
  border: 1px groove;
}
</style>
