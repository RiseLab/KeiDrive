<template>
    <div class="container postion-relative">
        <!--Alerts-->
        <div class="row">
            <div class="col">
                <div class="alert alert-danger" v-for="(error, index) in errors">
                    <strong>Error!</strong> {{ error.message }}
                    <button type="button" class="close" @click="errors.splice(index, 1)">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        <!--Upload control-->
        <div class="row mb-3">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span>
                            Upload new files
                        </span>
                        <div class="spinner-grow text-secondary" v-show="isUploading"></div>
                        <div>
                            <label class="btn btn-secondary btn-sm mb-0" :class="{'disabled': isUploading, 'c-pointer': !isUploading}" for="fileRaw">
                                <i class="fa fa-plus"></i>
                            </label>
                            <input type="file" id="fileRaw" class="d-none" multiple @change="addPendingFiles" :disabled="isUploading">
                            <button class="btn btn-secondary btn-sm" @click="clearPendingFiles" :disabled="isUploading">
                                <i class="fa fa-undo"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <span v-if="!pendingFiles.length">
                            No files to upload
                        </span>
                        <div class="card mb-2" v-for="(pendingFile, index) in pendingFiles">
                            <div class="card-body p-2">
                                <div class="mb-1">
                                    {{ pendingFile.raw.name }}
                                    <i v-if="pendingFile.state === 0 && !isUploading"
                                       class="fa fa-times text-danger c-pointer"
                                       @click="clearPendingFiles($event, index)"></i>
                                    <i v-else-if="pendingFile.state === 1"
                                       class="fa fa-spinner fa-spin text-secondary"></i>
                                    <span class="text-success"
                                          v-else-if="pendingFile.state === 2">
                                        Successfully uploaded <i class="fa fa-check"></i>
                                    </span>
                                </div>
                                <input type="text"
                                       class="form-control form-control-sm mb-2"
                                       placeholder="Title"
                                       v-model="pendingFiles[index].title">
                                <textarea class="form-control form-control-sm"
                                          rows="2"
                                          placeholder="Description"
                                          v-model="pendingFiles[index].description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-sm"
                                :disabled="!pendingFiles.length || isUploading"
                                @click="uploadFiles"
                        >Upload all</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Pagination and search-->
        <div class="row mb-3">
            <div class="col">
                <div class="card">
                    <div class="card-body pt-3 pb-2 form-inline">
                        Files:
                        {{ filesPerPage * (currentPage - 1) + 1}}-{{ filesPerPage * (currentPage - 1) + files.length }}
                        of
                        {{ filesTotal }}
                        <div class="flex-grow-1"></div>
                        <label for="filterPage" class="mr-1">Page</label>
                        <select class="form-control form-control-sm"
                                id="filterPage"
                                v-model="currentPage" @change="getFiles">
                            <option :value="n" v-for="n in pagesTotal">{{ n }}</option>
                        </select>
                        <label for="filterSearch" class="mr-1 ml-4">Filter</label>
                        <input type="text"
                               class="form-control form-control-sm mr-1"
                               id="filterSearch"
                               v-model="filter">
                        <button class="btn btn-primary btn-sm"
                                @click="getFiles">OK</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Files view-->
        <div class="row mb-3">
            <div class="col-md-6 position-relative mb-2" v-for="(file, index) in files">
                <div class="card d-flex flex-row">
                    <a class="d-block"
                       :href="`/files/download/${file.id}`"
                       target="_blank">
                        <img :src="file.icon" class="rounded-left" alt="" height="140">
                    </a>
                    <div class="card-body p-2">
                        <input type="text"
                               class="form-control form-control-sm mb-2"
                               placeholder="Title"
                               v-model="files[index].title">
                        <textarea class="form-control form-control-sm mb-2"
                                  rows="2"
                                  placeholder="Description"
                                  v-model="files[index].description">
                        </textarea>
                        <div class="d-flex flex-row">
                            <button class="btn btn-danger btn-sm"
                                    @click="deleteFile($event, file, index)">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                            <button class="btn btn-success btn-sm ml-1" @click="updateFile($event, file)">
                                Update
                            </button>
                            <div class="flex-grow-1"></div>
                            <a class="btn btn-primary btn-sm"
                               :href="`/files/download/${file.id}`"
                               :download="file.title">
                                <i class="fa fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div v-if="file.state > 0"
                         class="position-absolute d-flex h-100 w-100 align-items-center justify-content-center rounded-sm bg-ffffffcc">
                        <div class="spinner-border"
                             :class="{'text-success': file.state === 1, 'text-danger': file.state === 2}"
                             style="width: 3rem; height: 3rem;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FileBrowser",
        data() {
            return {
                files: [],
                filesTotal: 0,
                filesPerPage: 0,
                pagesTotal: 1,
                pendingFiles: [],
                uploadingCount: 0,
                currentPage: 1,
                filter: '',
                errors: []
            }
        },
        computed: {
            isUploading () {
                return this.uploadingCount > 0;
            }
        },
        methods: {
            addPendingFiles: function (e) {
                let files = e.target.files;
                for (let i = 0; i < files.length; i++) {
                    this.pendingFiles.push({
                        raw: files[i],
                        title: '',
                        description: '',
                        state: 0
                    });
                }
                e.target.value = '';
            },
            clearPendingFiles: function (e, index = null) {
                if (!index) {
                    this.pendingFiles = [];
                } else {
                    this.pendingFiles.splice(index, 1);
                }
            },
            uploadFiles: function () {
                this.uploadingCount = this.pendingFiles.length;
                for (let i = 0; i < this.pendingFiles.length; i++) {
                    let formData = new FormData();
                    formData.append('file', this.pendingFiles[i].raw);
                    formData.append('title', this.pendingFiles[i].title);
                    formData.append('description', this.pendingFiles[i].description);
                    this.pendingFiles[i].state = 1;
                    axios.post('/files', formData)
                            .then(response => {
                                this.pendingFiles[i].state = 2;
                            })
                            .catch(error => {
                                console.log(error.response.data.message);
                                this.errors.push(error.response.data);
                                this.pendingFiles[i].state = 0;
                            })
                            .finally(() => {
                                if (this.uploadingCount === 1) {
                                    let newPending = [];
                                    this.pendingFiles.forEach(function (item) {
                                        if (item.state !== 2) {
                                            newPending.push(item);
                                        }
                                    });
                                    setTimeout(() => {
                                        this.pendingFiles = newPending;
                                        this.getFiles();
                                    }, 1000);
                                }
                                this.uploadingCount--;
                            });
                }
            },
            getFiles: function () {
                if (this.filter) {
                    this.currentPage = 1;
                }
                axios.get('/files', {params: {page: this.currentPage, filter: this.filter}})
                        .then(response => {
                            this.files = response.data.files;
                            this.filesTotal = response.data.filesTotal;
                            this.filesPerPage = response.data.filesPerPage;
                            this.pagesTotal = response.data.pagesTotal;
                        })
                        .catch(error => {
                            console.log(error.response.data.message);
                            this.errors.push(error.response.data);
                        });
            },
            updateFile: function (e, file) {
                file.state = 1;
                axios.put(`/files/${file.id}`, {title: file.title, description: file.description})
                        .then(response => {
                            //
                        })
                        .catch(error => {
                            console.log(error.response.data.message);
                            this.errors.push(error.response.data);
                        })
                        .finally(() => {
                            file.state = 0;
                        });
            },
            deleteFile: function (e, file, index) {
                if (confirm(`Are you sure to delete file '${file.title}'?`)) {
                    file.state = 2;
                    axios.delete(`/files/${file.id}`)
                            .then(response => {
                                //this.files.splice(index, 1);
                                this.getFiles();
                            })
                            .catch(error => {
                                file.state = 0;
                                console.log(error.response.data.message);
                                this.errors.push(error.response.data);
                            });
                }
            }
        },
        mounted() {
            this.getFiles();
        }
    }
</script>
