<template>
    <div>
        <navegation></navegation>
        <br>

        <div class="tab-content">
            <!--<div v-if="isManager && !showAdd">
                <a class="btn btn-sm btn-primary" v-on:click.prevent="showAdd = true">Add New Item</a>
            </div>-->
            <div class="container-fluid">
                <h1 class="text-center mb-3">Tables</h1>
                <div class="row">
                    <div class="card col-sm-2 " style="width: 5rem">
                        <div class="btn align-content-center" @click="showAdd=true" style="font-size: 5rem">+</div>
                        <div class="card-body">
                            <center><a class="card-title" style="cursor: grab" v-if="!showAdd" @click="showAdd=true">Add
                                New Table</a></center>
                            <div class="form-group" v-if="showAdd">
                                <label for="inputId">Table Number</label>
                                <input
                                    type="number" class="form-control"
                                    name="name" id="inputId" v-model="newTableId"
                                    placeholder="Table Number" value="" required/>
                                <div class="btn-group-toggle">
                                    <a class="btn btn-primary float-left" v-on:click.prevent="addTable">Save</a>
                                    <a class="btn btn-light float-right" v-on:click.prevent="showAdd=false">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card col-sm-2 " style="width: 5rem " v-for="table in tables" :key="table.id">
                        <div class="align-content-center" style="font-size: 5rem">{{table.table_number}}</div>
                        <div class="card-body">
                            <h5 class="card-title" v-if="!canEdit(table)">Table {{table.table_number}}</h5>
                            <h5 class="card-title" v-else>Mesa <input
                                type="number" class="form-control"
                                placeholder="New Table Number" value="" v-model="newTableId" required/></h5>
                        </div>
                        <div class="form-group" v-if="!canEdit(table)">
                            <a class="btn btn-danger btn-sm" v-on:click.prevent="deleteTable(table)">Delete</a>
                            <a class="btn btn-primary btn-sm" v-on:click.prevent="showEdit(table.table_number)">Edit</a>
                        </div>
                        <div class="form-group" v-else>
                            <a class="btn btn-primary" v-on:click.prevent="updateTable(table)">Save</a>
                            <a class="btn btn-light" v-on:click.prevent="editing=false">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import navegation from './../navigation.vue';

    export default {
        components: {
            'navegation': navegation,
        },
        data: function () {
            return {
                title: "List Tables",
                tables: [],
                showAdd: false,
                editing: false,
                newTableId: null,
                currentTable: null
            }
        },
        methods: {
            getTables: function () {
                axios.get('api/tables').then(response => {
                    this.tables = response.data.data;
                });
            },
            addTable: function () {
                if (this.newTableId == null) {
                    this.toastPopUp('error', 'Table Number Required');
                } else {
                    axios.post('api/tables', {table_number: this.newTableId})
                        .then(res => {
                                this.getTables();
                                this.toastPopUp('success', 'Table added');
                                this.showAdd = false;
                                this.newTableId = null;
                            }
                        ).catch(error => this.toastPopUp("error", `${error.response.data.message}`))
                }

            },
            showEdit(tableNumber) {
                this.currentTable = tableNumber;
                this.newTableId = tableNumber;
                this.editing = true;
            },
            canEdit(table) {
                return this.editing == true && this.currentTable == table.table_number;
            },
            updateTable: function (table) {
                axios.put('api/tables/' + this.currentTable, {table_number: this.newTableId}).then(res => {
                    this.toastPopUp('success', 'Table Updated');
                    this.editing = false;
                    this.currentTable = null;
                    table.table_number = this.newTableId;
                    this.newTableId = null
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },

            deleteTable(table) {
                axios.delete('api/tables/' + table.table_number)
                    .then(response => {
                        this.getTables();
                        this.toastPopUp("success", "Table Deleted");
                    }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`
                    );
                });
            },

            toastPopUp(type, msg) {
                this.$toasted.clear();
                if (type == "show") {
                    this.$toasted.show(msg, {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
                if (type == "error") {
                    this.$toasted.error(msg, {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
                if (type == "success") {
                    this.$toasted.success(msg, {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
            },

        },
        computed: {
            isManager() {
                return this.$store.state.user && this.$store.state.user.type == "manager";
            },
        },

        mounted() {
            this.getTables();
        },
    }
</script>
