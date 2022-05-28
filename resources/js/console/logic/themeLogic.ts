import {actions, events, kea, key, listeners, path, props, reducers, selectors} from "kea";
import api from "../lib/api";
import {themeLogicType} from "./themeLogicType";
import {ajax} from "kea-ajax";
import {ThemeFile} from "../types";

const themeLogic = kea<themeLogicType>([

    props({} as {subdomain: string}),
    key(props => props.subdomain),
    path(key => ['theme', key]),
    actions(({values}) => ({
        setFiles: (files: ThemeFile[]) => ({files}),
        setFileContent: (id: number, content: string) => ({id, content}),

        // file editor
        editorOpenFile: (id: number) => ({id}),
        editorCloseFile: (id: number) => ({id, index: values.editorOpenedFilesIds.indexOf(id)}),
        editorSetOpenedFiles: (ids: number[]) => ({ids}),
        editorSaveFile: (id: number) => ({id}),
        editorSetActiveFileId: (id: number | null) => ({id}),
    })),

    ajax(({actions, props}) => ({

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
        }

    })),


    reducers({

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
        editorOpenedFilesIds: [
            [] as number[],
            {
                editorOpenFile: (state, {id}) => state.indexOf(id) === -1 ?
                    (state.length >= 8 ? state : [...state, id]) : state,

                editorCloseFile: (state, {id}) => state.filter(fileId => fileId !== id),

                editorSetOpenedFiles: (_, {ids}) => ids, // set all opened files (for sorting)
            }
        ],

        // active editing file
        editorActiveFileId: [
            null as null | number,
            {
                editorOpenFile: (_, {id}) => id,
                editorSetActiveFileId: (_, {id}) => id
            }
        ]

    }),

    listeners(({values, actions}) => ({

        editorCloseFile: ({id, index}) => {

            const previousFileId = values.editorOpenedFilesIds[index - 1];
            if (values.editorActiveFileId === id && previousFileId) {
                actions.editorSetActiveFileId(previousFileId)
            }

            if (values.editorOpenedFilesIds.length === 0) {
                actions.editorSetActiveFileId(null)
            }

        }

    })),

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