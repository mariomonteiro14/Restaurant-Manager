<template>
    <div>
    <navigation></navigation>
        <br>

        <div class="tab-content">
            <div v-if="isManager && !showAdd">
                <a class="btn btn-sm btn-primary" v-on:click.prevent="showAdd = true">Add New Item</a>
            </div>

            <item-add v-if="showAdd" @cancel = addCancel @save = addSaved></item-add>

            <div class="container-fluid">
                <h1 class="text-center mb-3">Drinks</h1>
                <div class="row">
                    <div class="card col-sm-2" style="width: 18rem" v-for="item in drinksOnly" :key="item.id">
                        <div style="height:200px; max-height:200px">
                            <img class="card-img-top" v-bind:src="getItemPhoto(item.photo_url)"/>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{item.name}}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{item.price}}€</h6>
                            <p class="card-text">{{item.description}}</p>
                        </div>
                        <div v-if="isManager">
                            <a class="btn btn-sm btn-primary" v-on:click.prevent="editItem(item)">Edit</a>
                            <a class="btn btn-sm btn-danger" v-on:click.prevent="deleteItem(item)">Delete</a>
                        </div>
                    </div>
                </div>
                <h1 class="text-center sb-2">Dishes</h1>
                <div class="row">
                    <div class="card col-sm-2" style="width: 18rem" v-for="item in dishesOnly" :key=item.id>
                        <div style="height:200px; max-height:200px">
                            <img class="card-img-top" v-bind:src="getItemPhoto(item.photo_url)" /> <!--width="306" height="204"-->
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{item.name}}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{item.price}}€</h6>
                            <p class="card-text">{{item.description}}</p>
                        </div>
                        <div v-if="isManager">
                            <a class="btn btn-sm btn-primary" v-on:click.prevent="editItem(item)">Edit</a>
                            <a class="btn btn-sm btn-danger" v-on:click.prevent="deleteItem(item)">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal Edit Ite,-->
        <div class="modal fade" id="editItemModal" tabindex="-1" role="dialog" aria-labelledby="addWorkerModal" aria-hidden="true" v-if="this.$store.state.user">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editItemModal">Edit Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container box">
                            <div class="form-group">
                                <label for="inputName">Name</label>
                                <input
                                    type="text" class="form-control"
                                    name="name" id="inputName" v-model="currentItem.name"
                                    placeholder="Name" value="" required/>
                            </div>

                            <div class="form-group">
                                <label for="Type">Type:</label>
                                <select class="form-control" id="type" name="type" v-model="currentItem.type">
                                    <option value="dish">dish</option>
                                    <option value="drink">drink</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="inputDescription">Description</label>
                                <textarea
                                    type="text" class="form-control"
                                    name="description" id="inputDescription"
                                    placeholder="Description" value="" v-model="currentItem.description"/>
                            </div>
                            <div class="form-group">
                                <label for="inputPrice">Price</label>
                                <input
                                    type="number" class="form-control"
                                    name="price" id="inputPrice"
                                    placeholder="Price" value="" v-model="currentItem.price" required/>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" v-on:change="handleFile">
                                <label class="custom-file-label" for="image">New Item Picture</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="save">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import navigation from './navigation.vue';
    import AddItem from './manager/AddItem.vue';

    export default {
        components: {
            'navigation': navigation,
            'item-add': AddItem,
        },
        data:function() {
            return {
                title: "List Items",
                items: [],
                showAdd: false,
                currentItem: {},
                newPhoto: null,
                errors: [],
        }},
        methods: {
            getItems: function(){
                axios.get('api/items').then(response=>{this.items = response.data.data;});
            },
            getItemPhoto(url){
                return "storage/items/" + url;
            },
            editItem(item){
                this.currentItem = item;
                $('#editItemModal').modal('show');
            },
            addSaved: function(){
                this.showAdd = false;
                this.getItems();
            },
            addCancel: function(){
                this.showAdd = false;
            },
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
                this.newPhoto = files[0];
                console.log(this.newPhoto);
            },

            checkForm: function () {
                this.errors = [];

                if (!this.currentItem.name) {
                    this.errors.push('Name required.');
                }
                if (!this.currentItem.type) {
                    this.errors.push('Type required.');
                }

                if (!this.currentItem.description) {
                    this.errors.push('Description required.');
                }
                if (!this.currentItem.price) {
                    this.errors.push('Price required.');
                }
            },
            formCreate: function(){
                let formData = new FormData();
                formData.append('name', this.currentItem.name);
                formData.append('type', this.currentItem.type);
                formData.append('description', this.currentItem.description);
                formData.append('price', this.currentItem.price);
                if (this.newPhoto != null) {
                    formData.append('newPhoto', this.newPhoto);
                }
                console.log(formData);
                return formData;

            },
            save: function() {
                this.checkForm();
                if (this.errors.length == 0) {
                    axios.post('api/items/' + this.currentItem.id, this.formCreate())
                        .then(response => {
                            $('#editItemModal').modal('hide');
                            console.log(response.data);
                            this.currentItem = response.data;
                            this.toastPopUp("success", "Item Saved");
                            if(this.newPhoto){
                                this.getItems();
                                this.newPhoto = null;
                            }
                        }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`
                        );
                    })
                }else{
                    this.toastPopUp("show", "Fill all the fields");
                }
            },
            deleteItem(item){
                axios.delete('api/items/'+item.id)
                    .then(response => {
                        this.getItems();
                        this.toastPopUp("success", "Item Deleted");
                    });
            },

        },
        computed: {
            drinksOnly: function(){
                var drinks = [];
                for(let i in this.items){
                    if(this.items[i].type == "drink"){
                        drinks.push(this.items[i]);
                    }
                }
            return drinks;
            },
            dishesOnly: function(){
                var dishes = [];
                for(let i in this.items){
                    if(this.items[i].type == "dish"){
                        dishes.push(this.items[i]);
                    }
                }
            return dishes;
            },
            isManager(){
                return this.$store.state.user && this.$store.state.user.type == "manager";
            },



        },

        mounted(){
            this.getItems();
        },
	}
</script>
