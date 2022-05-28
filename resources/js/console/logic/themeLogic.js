import { kea } from "kea";
import api from "../lib/api";


const themeLogic = kea({

    key: props => props.subdomain,

    path: key => ['theme', key],

    actions: ({values}) => ({
        setFiles: (files) => ({files}),
        setFileContent: (id, content) => ({id, content}),

        // file editor
        editorOpenFile: (id) => ({id}),
        editorCloseFile: (id) => ({id, index: values.editorOpenedFilesIds.indexOf(id)}),
        editorSetOpenedFiles: (ids) => ({ids}),
        editorSaveFile: (id) => ({id}),
        editorSetActiveFileId: (id) => ({id}),
    }),

    ajax: ({actions, props}) => ({

        loadFiles: async () => {
            const files = await api.get(props.subdomain, '/theme/files');
            actions.setFiles(files);
        },

    }),


    reducers: {

        files: [[], {
            setFiles: (_, {files}) => files,
            setFileContent: (state, {id, content}) => state.map(file => file.id === id ? {...file, ...{content}} : file),
        }],

        originalFiles: [[], {
            setFiles: (_, {files}) => files
        }],

        // opened files (only ID)
        editorOpenedFilesIds: [[], {
            editorOpenFile: (state, {id}) => state.indexOf(id) === -1 ?
                (state.length >= 8 ? state : [...state, id]) : state,

            editorCloseFile: (state, {id}) => state.filter(fileId => fileId !== id),

            editorSetOpenedFiles: (_, {ids}) => ids, // set all opened files (for sorting)
        }],

        // active editing file
        editorActiveFileId: [null, {
            editorOpenFile: (_, {id}) => id,
            editorSetActiveFileId: (_, {id}) => id
        }]

    },

    listeners: ({values, actions}) => ({

        editorCloseFile: ({id, index}) => {

            const previousFileId = values.editorOpenedFilesIds[index - 1];
            if (values.editorActiveFileId === id && previousFileId) {
                actions.editorSetActiveFileId(previousFileId)
            }

            if (values.editorOpenedFilesIds.length === 0) {
                actions.editorSetActiveFileId(null)
            }

        }

    }),

    selectors: {
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
                const currentFile = files.find(file => file.id === id);
                const originalFile = originalFiles.find(file => file.id === id);

                return currentFile.content !== originalFile.content;
            }
        ]
    },

    events: ({actions}) => ({
        afterMount: actions.loadFiles
    })

});

export default themeLogic;