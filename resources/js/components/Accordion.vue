<template>
  <div>
    <div class="border border-gray-200">
      <h2 class="mb-0">
        <button
          @click="toggleView()"
          class="relative flex items-center w-full py-4 px-5 text-base text-gray-800 text-left bg-white border-0 rounded-none transition focus:outline-none"
          type="button"
        >
          <slot name="title"></slot>
          <svg
            v-if="show"
            class="ml-auto mr-0 h-6 w-6"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M5 15l7-7 7 7"
            />
          </svg>
          <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            class="ml-auto mr-0 h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>
      </h2>
      <div>
        <!-- <div class="py-4 px-5 flex">
          <slot name="alwaysShow"></slot>
        </div> -->
        <div v-show="show">
          <slot name="toggleable"></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["resources"],
  data() {
    return {
      show: false,
    };
  },

  methods: {
    toggleView() {
      this.show = !this.show;
    },
  },

  // mounted() {
  //   this.$el.parentElement.parentElement.classList.add('w-full')
  //   this.$parent.$parent.$parent.$el.parentElement.classList.add('w-max')
  // }
};
</script>
