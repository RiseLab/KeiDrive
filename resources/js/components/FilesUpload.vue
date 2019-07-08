<template>
    <div class="container">
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
                                    <i v-if="pendingFile.status === 0 && !isUploading"
                                       class="fa fa-times text-danger c-pointer"
                                       @click="clearPendingFiles($event, index)"></i>
                                    <i v-else-if="pendingFile.status === 1"
                                       class="fa fa-spinner fa-spin text-secondary"></i>
                                    <span class="text-success"
                                          v-else-if="pendingFile.status === 2">
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
        <div class="row mb-3">
            <div class="col-6 mb-2" v-for="(file, index) in files">
                <div class="card d-flex flex-row">
                    <div>
                        <img :src="file.icon" alt="">
                    </div>
                    <div class="card-body p-2">
                        <input type="text"
                               class="form-control form-control-sm mb-2"
                               placeholder="Title"
                               v-model="files[index].title">
                        <textarea class="form-control form-control-sm"
                                  rows="2"
                                  placeholder="Description"
                                  v-model="files[index].description">
                        </textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FilesUpload",
        data() {
            return {
                files: [],
                pendingFiles: [],
                uploadingCount: 0,
            }
        },
        computed: {
            isUploading () {
                return this.uploadingCount > 0;
            },
        },
        methods: {
            addPendingFiles: function (e) {
                let files = e.target.files;
                for (let i = 0; i < files.length; i++) {
                    this.pendingFiles.push({
                        raw: files[i],
                        title: '',
                        description: '',
                        status: 0
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
                    this.pendingFiles[i].status = 1;
                    axios.post('/files', formData)
                            .then(response => {
                                this.pendingFiles[i].status = 2;
                            })
                            .catch(error => {
                                console.log(error.response.data.message);
                                this.pendingFiles[i].status = 0;
                            })
                            .finally(() => {
                                if (this.uploadingCount === 1) {
                                    let newPending = [];
                                    this.pendingFiles.forEach(function (item) {
                                        if (item.status !== 2) {
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
                axios.get('/files')
                        .then(response => {
                            this.files = response.data;
                        })
                        .catch(error => {
                            console.log(error.response.data.message);
                        });
            }
        },
        mounted() {
            this.getFiles();
        }
    }
</script>
