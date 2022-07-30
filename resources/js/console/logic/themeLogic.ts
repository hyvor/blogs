import {actions, events, kea, key, listeners, path, props, reducers, selectors} from "kea";
import api, {getMiscEndpoint} from "../lib/api";
import {themeLogicType} from "./themeLogicType";
import {ajax} from "kea-ajax";
import {Theme, ThemeFile, ThemeFolder} from "../types";
import axios from "axios";

const themeLogic = kea<themeLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['theme', key]),
    actions(({values}) => ({
        setThemes: (themes: Theme[]) => ({themes}),

        setFiles: (files: ThemeFile[]) => ({files}),
        setFileContent: (id: number, content: string) => ({id, content}),
        saveFileContent: (id: number, content: string) => ({id, content}),

        // file editor
        editorOpenFile: (id: number) => ({id}),
        editorCloseFile: () => false,
        editorSaveFile: (id: number) => ({id}),
    })),

    ajax(({actions, props, values}) => ({

        loadThemes: async () => {
            const res = await axios.get(getMiscEndpoint('/themes'))
            actions.setThemes(res.data as Theme[])
        },

        changeTheme: async ({name, onChange} : {name: string, onChange: Function}) => {
            const res = await api.patch<ThemeFile[]>(props.subdomain, `/theme`, {
                name
            })
            actions.setFiles(res)
            onChange();
        },

        loadFiles: async () => {
            const files = await api.get<ThemeFile[]>(props.subdomain, '/theme/files');
            actions.setFiles(files);
        },

        uploadTheme: async({zip, onUpload} : {zip: File, onUpload: Function}) => {
            const formData = new FormData()
            formData.append('zip', zip)
            const files = await api.post<ThemeFile[]>(props.subdomain, '/theme', formData);
            actions.setFiles(files)
            onUpload();
        },

        createFile: async (
            {name, folder, content, onCreate} :
            {name: string, folder: ThemeFolder, content: string | Blob, onCreate: Function}
        ) => {

            const formData = new FormData()
            formData.append('name', name)
            formData.append('folder', folder || '');
            formData.append(content instanceof Blob ? 'file' : 'content', content)

            const file = await api.post<ThemeFile>(props.subdomain, '/theme/file', formData);
            actions.setFiles([...values.files, file]);

            onCreate();
        },

        updateFile: async({id, name, content} : {id: number, name?: string, content?: string}) => {

            const data = {} as {name?: string, content?: string};
            if (name !== undefined)
                data.name = name
            if (content !== undefined) {
                data.content = content;
            }

            const file = await api.patch<ThemeFile>(props.subdomain, `/theme/file/${id}`, data)
            actions.setFiles(values.files.map(f => f.id === file.id ? file : f))

        },


        deleteFile: async({id} : {id: number}) => {
            actions.setFiles(values.files.filter(f => f.id !== id))
            await api.delete(props.subdomain, `/theme/file/${id}`)
        }

    })),


    reducers({

        themes: [
            [] as Theme[],
            {
                setThemes: (_, {themes}) => themes
            }
        ],

        files: [
            [] as ThemeFile[],
            {
                setFiles: (_, {files}) => files,
                setFileContent: (state, {id, content}) => state.map(file => file.id === id ? {...file, ...{content}} : file),
            }
        ],

        originalFiles: [
            [] as ThemeFile[],
            {
                setFiles: (_, {files}) => files
            }
        ],

        // opened files (only ID)
        editorOpenedFileId: [
            null as null | number,
            {
                editorOpenFile: (state, {id}) => state === id ? null : id,
                editorCloseFile: () => null,
            }
        ],

    }),

    selectors({
        findFilesOfFolder: [
            s => [s.files],
            files => folder => files.filter(file => file.folder === folder).sort((a,b) => a.name > b.name ? 1 : -1)
        ],
        getFileById: [
            s => [s.files],
            files => id => files.find(file => file.id === id)
        ],

        hasFileUpdated: [
            s => [s.files, s.originalFiles],
            (files, originalFiles) => id => {
                const currentFile = files.find(file => file.id === id) as ThemeFile;
                const originalFile = originalFiles.find(file => file.id === id) as ThemeFile;

                return currentFile.content !== originalFile.content;
            }
        ]
    }),

    events(({actions}) => ({
        afterMount: actions.loadFiles
    }))

]);

export default themeLogic;