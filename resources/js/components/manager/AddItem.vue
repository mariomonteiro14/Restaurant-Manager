<template>
    <div class="jumbotron">
        <h2>Add Item</h2>
        <div v-if="errors.length">
                <b>Please correct the following error(s):</b>
            <ul>
                <li v-for="error in errors">{{ error }}</li>
            </ul>
        </div>
        <div class="form-group">
            <label for="inputName">Name</label>
            <input
                type="text" class="form-control"
                name="name" id="inputName" v-model="name"
                placeholder="Name" value="" required/>
        </div>

        <div class="form-group">
            <label for="Type">Type:</label>
            <select class="form-control" id="type" name="type" v-model="type" required>
                <option value="dish">dish</option>
                <option value="drink">drink</option>
            </select>
        </div>

        <div class="form-group">
            <label for="inputDescription">Description</label>
            <textarea
                type="text" class="form-control"
                name="description" id="inputDescription" required
                placeholder="Description" value="" v-model="description"/>
        </div>
        <div class="form-group">
            <label for="inputPrice">Price</label>
            <input
                type="number" class="form-control"
                name="price" id="inputPrice"
                placeholder="Price" value="" v-model="price" required/>
        </div>

        <div>
            <label for="inputImage">Image</label>
            <input class="form-control-file" type="file" id="inputImage" accept="image/*" @change="handleFile">
        </div>
        <div class="form-group">
            <a class="btn btn-primary" v-on:click.prevent="save">Save</a>
            <a class="btn btn-light" v-on:click.prevent="cancel">Cancel</a>
        </div>

    </div>
</template>
<script>

    module.exports = {

        data: function() {
            return {
                name : null,
                description : null,
                type : null,
                price : null,
                photo_url : null,
                errors: [],
            };
        },
        methods: {
            handleFile: function(e){
                var files = e.target.files || e.dataTransfer.files;
                if(files.length != 1){
                    this.$toasted.error('Only one file allowed', {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
                if (!files[0].type.includes("image/")) {
                    this.$toasted.error('File must be an image', {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
                this.photo_url = files[0];
            },
            formCreate: function(){
                let formData = new FormData();
                formData.append('name', this.name);
                formData.append('type', this.type);
                formData.append('description', this.description);
                formData.append('price', this.price);
                formData.append('photo_url', this.photo_url);
                return formData;
            },
            checkForm: function () {
                this.errors = [];

                if (!this.name) {
                    this.errors.push('Name required.');
                }
                if (!this.type) {
                    this.errors.push('Type required.');
                }

                if (!this.description) {
                    this.errors.push('Description required.');
                }
                if (!this.price) {
                    this.errors.push('Price required.');
                }
                if (!this.photo_url) {
                    this.errors.push('Image required.');
                }
            },
            save: function() {
                this.checkForm();
                if (this.errors.length == 0) {
                    axios.post('/api/items', this.formCreate())
                        .then(response => {
                        this.$emit('save');
                            this.toastPopUp("success", "Item Added")
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    })
                }
            },
            toastPopUp(type, msg){
                this.$toasted.clear();
                if(type == "show"){
                    this.$toasted.show(msg, {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
                if(type == "error"){
                    this.$toasted.error(msg, {
                        position: "bottom-center",
                        duration: 10000000,
                    });
                    return;
                }
                if(type == "success"){
                    this.$toasted.success(msg, {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
            },
            cancel: function(){
                this.$emit('cancel')
            }
        },
    };
</script>
