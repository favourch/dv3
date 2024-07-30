<template>
    <AppLayout>
        <div class="md:bg-inherit bg-white md:flex md:flex-grow capitalize">
            <div class="md:w-[30%] flex-col h-full bg-white border-r border-l md:flex" :class="$page.url === '/contacts/add' || contact ? 'hidden' : ''">
                <div class="px-4 pt-4">
                    <div class="flex justify-between mt-2">
                        <div class="flex space-x-1 text-xl">
                            <h2>{{ $t('Contacts') }}</h2>
                            <span class="text-slate-500">{{ props.rowCount }}</span>
                        </div>
                        <div class="flex space-x-2 items-center">
                            <button @click="isOpenModal = true" title="Import">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16"><path fill="currentColor" fill-rule="evenodd" d="M11.78 7.159a.75.75 0 0 0-1.06 0l-1.97 1.97V1.75a.75.75 0 0 0-1.5 0v7.379l-1.97-1.97a.75.75 0 0 0-1.06 1.06l3.25 3.25L8 12l.53-.53l3.25-3.25a.75.75 0 0 0 0-1.061M2.5 9.75a.75.75 0 1 0-1.5 0V13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9.75a.75.75 0 0 0-1.5 0V13a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z" clip-rule="evenodd"/></svg>
                            </button>
                            <a href="/contacts/export" title="Export">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M11.5 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v5m-5 6h7m-3-3l3 3l-3 3"/></g></svg>
                            </a>
                            <Link href="/contacts/add" title="Add Contact">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"><path d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12Zm10-8a8 8 0 1 0 0 16a8 8 0 0 0 0-16Z"/><path d="M13 7a1 1 0 1 0-2 0v4H7a1 1 0 1 0 0 2h4v4a1 1 0 1 0 2 0v-4h4a1 1 0 1 0 0-2h-4V7Z"/></g></svg>
                            </Link>
                        </div>
                    </div>
                </div>
                <ContactTable :rows="props.rows" :filters="props.filters" :type="'contact'" @callback="handleContact"/>
            </div>
            <div class="md:w-[70%] bg-cover md:h-[100vh] md:overflow-y-hidden">
                <div v-if="contact">
                    <ContactInfo v-if="!editContact" class="pt-20" :contact="contact" :fields="props.fields" :locationSettings="locationSettings"/>
                    <ContactForm v-else :contactGroups="props.contactGroups" :contact="props.contact" :fields="props.fields" :locationSettings="locationSettings" />
                </div>
                <div v-else>
                    <div v-if="$page.url === '/contacts/add'">
                        <ContactForm :contactGroups="props.contactGroups" :contact="props.contact" :fields="props.fields" :locationSettings="locationSettings" />
                    </div>
                    <div v-else>
                        <div class="md:flex justify-center pt-20 hidden">
                            <div class="border pt-20 py-10 w-[30em] rounded-xl bg-white">
                                <h2 class="text-center text-2xl text-slate-500 mb-6">{{ $t('Select contact') }}</h2>
                                <div class="flex justify-center">
                                    <div class="border-r border-slate-500 h-10"></div>
                                </div>
                                <h2 class="text-center text-slate-600">{{ $t('Or') }}</h2>
                                <div class="flex justify-center">
                                    <div class="border-r border-slate-500 h-10"></div>
                                </div>
                                <div class="flex justify-center space-x-4 mt-6">
                                    <Link href="/contacts/add" class="bg-primary rounded-lg text-sm text-white p-2 px-8 text-center">{{ $t('Add contact') }}</Link>
                                    <button @click="isOpenModal = true" class="bg-primary rounded-lg text-sm text-white p-2 px-8 text-center">{{ $t('Bulk upload') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    <!-- Import Modal-->
    <Modal :label="modalLabel" :isOpen=isOpenModal>
        <span class="text-sm text-slate-600">{{ $t('Upload a csv to import your contact data') }}. For the phone field ensure that you start with the contact's country code.</span>
        <br>
        <br>
        <span class="text-sm text-slate-600 underline flex justify-center">
            <a href="/contacts/export">{{ $t('Click here to download sample csv template') }}</a>
        </span>
        <div class="max-w-md w-full space-y-8">
            <div class="mt-8 space-y-6">
                <div @dragover.prevent @drop="handleDrop" class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <input
                        type="file"
                        class="sr-only"
                        accept=".csv"
                        ref="fileInput"
                        id="file-upload"
                        @change="handleFileUpload($event)"
                    />
                    <div class="text-center">
                        <div>
                            <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><path fill="currentColor" d="m15.393 4.054l-.502.557l.502-.557Zm3.959 3.563l-.502.557l.502-.557Zm2.302 2.537l-.685.305l.685-.305ZM3.172 20.828l.53-.53l-.53.53Zm17.656 0l-.53-.53l.53.53ZM14 21.25h-4v1.5h4v-1.5ZM2.75 14v-4h-1.5v4h2.5Zm18.5-.437V14h2.5v-.437h-1.5ZM14.891 4.61l3.959 3.563l1.003-1.115l-3.958-3.563l-1.004 1.115Zm7.859 8.952c0-1.689.015-2.758-.41-3.714l-1.371.61c.266.598.281 1.283.281 3.104h2.5Zm-3.9-5.389c1.353 1.218 1.853 1.688 2.119 2.285l1.37-.61c-.426-.957-1.23-1.66-2.486-2.79L18.85 8.174ZM10.03 2.75c1.582 0 2.179.012 2.71.216l.538-1.4c-.852-.328-1.78-.316-3.248-.316v1.5Zm5.865.746c-1.086-.977-1.765-1.604-2.617-1.93l-.537 1.4c.532.204.98.592 2.15 1.645l1.004-1.115ZM10 21.25c-1.907 0-3.261-.002-4.29-.14c-1.005-.135-1.585-.389-2.008-.812l-1.06 1.06c.748.75 1.697 1.081 2.869 1.239c1.15.155 2.625.153 4.489.153v-1.5ZM1.25 14c0 1.864-.002 3.338.153 4.489c.158 1.172.49 2.121 1.238 2.87l1.06-1.06c-.422-.424-.676-1.004-.811-2.01c-.138-1.027-.14-2.382-.14-4.289h-1.5ZM14 22.75c1.864 0 3.338.002 4.489-.153c1.172-.158 2.121-.49 2.87-1.238l-1.06-1.06c-.424.422-1.004.676-2.01.811c-1.027.138-2.382.14-4.289.14v1.5ZM21.25 14c0 1.907-.002 3.262-.14 4.29c-.135 1.005-.389 1.585-.812 2.008l1.06 1.06c.75-.748 1.081-1.697 1.239-2.869c.155-1.15.153-2.625.153-4.489h-1.5Zm-18.5-4c0-1.907.002-3.261.14-4.29c.135-1.005.389-1.585.812-2.008l-1.06-1.06c-.75.748-1.081 1.697-1.239 2.869C1.248 6.661 1.25 8.136 1.25 10h2.5Zm7.28-8.75c-1.875 0-3.356-.002-4.511.153c-1.177.158-2.129.49-2.878 1.238l1.06 1.06c.424-.422 1.005-.676 2.017-.811c1.033-.138 2.395-.14 4.312-.14v-1.5Z"/><path stroke="currentColor" stroke-width="1.5" d="M13 2.5V5c0 2.357 0 3.536.732 4.268C14.464 10 15.643 10 18 10h4"/></g></svg>
                        <div class="flex text-sm text-gray-600">
                            <label
                            for="file-upload"
                            class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                            >
                            <span>{{ $t('Click to upload a file') }}</span>
                            </label>
                            <p class="pl-1">{{ $t('Or drag and drop') }}</p>
                        </div>
                        <p class="text-xs text-gray-500">{{ $t('CSV files only') }}</p>
                        </div>
                    </div>
                </div>
                <div v-if="uploads.length" class="mt-4">
                <ul class="mt-2">
                    <li v-for="upload in uploads" :key="upload.name" class="bg-slate-50 px-6 py-2 flex rounded-md justify-between items-center mb-1">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-indigo-600 truncate">{{ upload.name }}</p>
                            <div class="mt-1 text-sm text-gray-500">
                                <span v-if="upload.progress !== 100">{{ upload.progress }}% - </span>
                                <div v-else>
                                    <div class="text-green-800 flex items-center gap-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M14.25 8.75c-.5 2.5-2.385 4.854-5.03 5.38A6.25 6.25 0 0 1 3.373 3.798C5.187 1.8 8.25 1.25 10.75 2.25"/><path d="m5.75 7.75l2.5 2.5l6-6.5"/></g></svg>
                                        {{ (upload.totalImports - (upload.totalFailedDuplicates + upload.totalFailedFormats)) + '/' + upload.totalImports }} {{ $t('Contacts added successfully!') }}
                                    </div>
                                    <div v-if="upload.totalFailedDuplicates > 0" class="text-red-400 flex items-center gap-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M15.71 8.29a1 1 0 0 0-1.42 0L12 10.59l-2.29-2.3a1 1 0 0 0-1.42 1.42l2.3 2.29l-2.3 2.29a1 1 0 0 0 0 1.42a1 1 0 0 0 1.42 0l2.29-2.3l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42L13.41 12l2.3-2.29a1 1 0 0 0 0-1.42m3.36-3.36A10 10 0 1 0 4.93 19.07A10 10 0 1 0 19.07 4.93m-1.41 12.73A8 8 0 1 1 20 12a7.95 7.95 0 0 1-2.34 5.66"/></svg>
                                        {{ upload.totalFailedDuplicates }} Duplicates found
                                    </div>
                                    <div v-if="upload.totalFailedFormats > 0" class="text-red-400 flex items-center gap-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M15.71 8.29a1 1 0 0 0-1.42 0L12 10.59l-2.29-2.3a1 1 0 0 0-1.42 1.42l2.3 2.29l-2.3 2.29a1 1 0 0 0 0 1.42a1 1 0 0 0 1.42 0l2.29-2.3l2.29 2.3a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.42L13.41 12l2.3-2.29a1 1 0 0 0 0-1.42m3.36-3.36A10 10 0 1 0 4.93 19.07A10 10 0 1 0 19.07 4.93m-1.41 12.73A8 8 0 1 1 20 12a7.95 7.95 0 0 1-2.34 5.66"/></svg>
                                        {{ upload.totalFailedFormats }} formatting issues found
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <div v-if="upload.progress !== 100" class="relative w-48">
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-indigo-200">
                                    <div :style="{ width: `${upload.progress}%` }" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                </div>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-x-6 gap-y-4">
            <div class="mt-2 w-full">
                <button type="button" @click="isOpenModal = false" class="inline-flex float-right justify-center rounded-md border border-transparent bg-slate-50 px-4 py-2 text-sm text-slate-500 hover:bg-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">{{ $t('Close') }}</button>
            </div>
        </div>
    </Modal>
</template>
<script setup>
    import AppLayout from "./../Layout/App.vue";
    import { ref } from 'vue';
    import { Link } from "@inertiajs/vue3";
    import ContactForm from '@/Components/ContactComponents/CreateForm.vue';
    import ContactInfo from '@/Components/ContactInfo.vue';
    import ContactTable from '@/Components/Tables/ContactTable.vue';
    import Modal from '@/Components/Modal.vue';
    import { router } from '@inertiajs/vue3';
    import { trans } from 'laravel-vue-i18n';

    const props = defineProps({ rows: Object, filters: Object, rowCount: Number, contactGroups: Object, contact: Object, editContact: Boolean, fields: Object, locationSettings: String });
    const uploads = ref([]);
    const isOpenModal = ref(false);
    const modalLabel = trans('import contacts');
    const uploadCount = ref(0);

    const handleContact = (value) => {
        router.visit('/contacts', {
            method: 'get',
            data: value,
        })
    }

    const handleFileUpload = (event) => {
        const files = event.target.files;
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(file.type)) {
                alert(trans('please select a CSV or XLSX file'));
                return;
            }
            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    const progress = Math.round((event.loaded / event.total) * 100);
                    uploads.value[uploadCount.value].progress = progress;
                }
            });

            xhr.open('POST', '/contacts/import');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

            xhr.onload = () => {
                if (xhr.status === 200) {
                    // Handle success
                    var jsonResponse = JSON.parse(xhr.response);
                    uploads.value[uploadCount.value].totalImports = jsonResponse.totalImports;
                    uploads.value[uploadCount.value].totalFailedDuplicates = jsonResponse.failedDuplicates;
                    uploads.value[uploadCount.value].totalFailedFormats = jsonResponse.failedFormats;
                    uploadCount.value++;
                    //console.log('File uploaded successfully!');
                } else {
                    // Handle error
                    //console.error('File upload failed.');
                }
            };

            xhr.send(formData);

            uploads.value.push({
                name: file.name,
                progress: 0,
            });
        }
    };

    const handleDrop = (event) => {
        event.preventDefault();
        const files = event.dataTransfer.files;
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(file.type)) {
                alert(trans('please select a CSV or XLSX file'));
                return;
            }
            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    const progress = Math.round((event.loaded / event.total) * 100);
                    uploads.value[uploadCount.value].progress = progress;
                }
            });

            xhr.open('POST', '/contacts/import');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

            xhr.onload = () => {
                if (xhr.status === 200) {
                    // Handle success
                    var jsonResponse = JSON.parse(xhr.response);
                    uploads.value[uploadCount.value].totalImports = jsonResponse.totalImports;
                    uploads.value[uploadCount.value].totalFailedDuplicates = jsonResponse.failedDuplicates;
                    uploads.value[uploadCount.value].totalFailedFormats = jsonResponse.failedFormats;
                    uploadCount.value++;
                    //console.log('File uploaded successfully!');
                } else {
                    // Handle error
                    //console.error('File upload failed.');
                }
            };

            xhr.send(formData);
            
            uploads.value.push({
                name: file.name,
                progress: 0,
            });
        }
    };
</script>