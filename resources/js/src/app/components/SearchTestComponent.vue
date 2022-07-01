<template>
  <div>
    <h1>Hello, this is search test component</h1>
    <p class="fl-red-text">{{ sayHello('Plenty') }}</p>
  </div>
</template>

<script lang="ts">
export default {
  name: 'SearchTestComponent',
  methods: {
    sayHello(name: string){
      return 'Hello ' + name;
    }
  }
};
</script>

<style scoped lang="scss">
.fl-red-text {
  color: red;
}
</style>
