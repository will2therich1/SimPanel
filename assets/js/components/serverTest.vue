<template>
    <th>
          <a href='' @click="testServer">Test</a>
    </th>
</template>

<script>
    console.log('Loading server test Vue file');

  export default {
    name: 'serverTest',
    props: ['serverid'],
    methods: {
        testServer() {
            var testUrl = './network/' + this.serverid + '/connectiontest';
            console.log(testUrl);
        },
    },

  }
</script>

<style scoped>

</style>