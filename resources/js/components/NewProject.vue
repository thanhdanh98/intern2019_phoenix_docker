<template>
    <modal name="new-project" classes="p-10 bg-card rounded-lg" height="auto">
        <h1 class="font-normal mb-16 text-center text-default">New Project</h1>
        <form @submit.prevent="submit">
            <div class="flex text-default">
                <div class="flex-1 mr-4">
                    <div class="mb-4">
                        <label for="title" class="text-sm block mb-2">Title</label>
                        <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        :class="errors.title ? 'border-red-500' : ''"
                        class="border bg-page p-2 text-xs block w-full rounded" 
                        v-model="form.title"
                        >
                        <span class="text-xs text-red-500 italic" v-if="errors.title" v-text="errors.title[0]"></span>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="text-sm text-default block mb-2">Description</label>
                        <textarea 
                            type="text" 
                            id="description" 
                            name="description" 
                            rows="7"
                            :class="errors.title ? 'border-red-500' : ''"
                            class="border bg-page p-2 text-xs block w-full rounded" 
                            v-model="form.description">
                        </textarea>
                        <span class="text-xs text-red-500 italic" v-if="errors.description" v-text="errors.description[0]"></span>
                    </div>
                </div>

                <div class="flex-1 mr-4">
                    <div class="mb-4">
                        <label class="text-sm text-default block mb-2">Task</label>
                        <input 
                            type="text" 
                            class="border bg-page mb-2 p-2 text-xs block w-full rounded" 
                            placeholder="Task One" 
                            v-for="task in form.tasks"
                            v-model="task.body"
                            :key="task.id">
                    </div>

                    <button type="button" class="inline-flex items-center text-default" @click="addTask">
                        <i class="fas fa-plus-circle  mr-2"></i>
                        <span>Add new task</span>
                    </button>
                </div>
            </div>

            <footer class="flex justify-end ">
                <button class="btn mr-2">Create Project</button>
                <button type="button"  class="btn"  @click="$modal.hide('new-project')">Cancel</button>
            </footer>
        </form>
    </modal>
</template>

<script>
export default {
    data() {
        return {
            form : {
                title : '',
                description : '',
                tasks : [
                    { body : '' },
                ]
            },

            errors : []
        }
    },
    methods : {
        addTask() {
            this.form.tasks.push({ value : '' });
        },

        submit() {
            axios.post('/projects' , this.form)
            .then(res => {
                location = res.data.message;
            }).catch(error => {
                this.errors = error.response.data.errors;
            });
        }
    }
}
</script>

<style>

</style>
