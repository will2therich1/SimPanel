<template>
    <a href='' @click.prevent="testServer">Test</a>
</template>

<script>
    console.log('Loading server test Vue file');

  export default {
    props: ['serverid'],
      methods: {
          testServer() {
              var testUrl = '/admin/network/' + this.serverid + '/test';
              axios.get(testUrl)
                  .then(function (response){
                    let responseData = JSON.parse(response.data);
                    if (responseData.connected == false){
                        alert("Failed to connect to server");
                    }else{
                        alert("Connected to server");
                    }
                  });
          },
      },
  }
</script>

<style scoped>

</style>