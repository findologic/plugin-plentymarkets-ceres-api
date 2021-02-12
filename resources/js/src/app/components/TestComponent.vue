<template>
  <div>
    <h1>Hello World!</h1>
    <span class="fl-red-text">This should be red. Test: {{ typedMethod('Hi Mom!') }}</span>
  </div>
</template>

<script lang="ts">
export default {
  name: "TestComponent",
  methods: {
    typedMethod(something: string) {
      return something;
    }
  }
}
</script>

<style scoped lang="scss">
.fl-red-text {
  color: red;
}
</style>
