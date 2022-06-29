import React, { useEffect, useState} from 'react'
import {
    Popup,
    PopupBodyDefault, PopupConfirm,
    PopupFooterDoubleButton,
    PopupFooterSingleButton,
    PopupHeaderDefault
} from "../ReusableComponents/Popup";
import {useThemeActions, useThemeValues} from "./use";
import Loader from "../ReusableComponents/Loader";
import {Theme} from "../types";
import {BoxArrowUpRight} from "react-bootstrap-icons";
import {toast} from "react-toastify";

export default function Changer() {

    const [isChanging, setIsChanging] = useState(false);

    const { loadThemes, changeTheme } = useThemeActions();
    const { themes, loadThemesAjax, changeThemeAjax } = useThemeValues()

    useEffect(() => {
        loadThemesAjax.status !== 'success' && loadThemes();
    }, [])

    function handleSelect(name: string) {
        changeTheme({name, onChange: () => {
            toast.success("Theme changed");
            setIsChanging(false)
        }});
    }

    return <span className="theme-changer">
        <a
            className="button small"
            onClick={() => setIsChanging(true)}
        >Change</a>

        {
            isChanging &&
                <Popup
                    header={<PopupHeaderDefault title={
                        <div className="theme-change-title">
                            <div>Select a Theme</div>
                            <div className="preview-themes-row"><a
                                className="link"
                                href="/themes"
                                target="_blank"
                            >Preview Themes <BoxArrowUpRight /></a></div>
                        </div>
                    } />}
                    body={<PopupBodyDefault>
                        {
                            loadThemesAjax.status !== 'success' ?
                            <Loader padding={60} /> :
                            <ThemesList
                                themes={themes}
                                onSelect={handleSelect}
                            />
                        }
                    </PopupBodyDefault>}
                    footer={
                        loadThemesAjax.status === 'success' ?
                            <PopupFooterDoubleButton
                                name="Change"
                                onClick={() => {}}
                                onCancel={() => setIsChanging(false)}
                                isLoading={changeThemeAjax.status === 'loading'}
                                loadingName="Changing"
                            /> :
                            <PopupFooterSingleButton
                                onClick={() => setIsChanging(false)}
                                name="Cancel"
                                buttonClass="text-only"
                            />
                    }
                />
        }
    </span>

}

function ThemesList({ themes, onSelect } : { themes: Theme[], onSelect: (name: string) => void}) {

    const [selectingTheme, setSelectingTheme] = useState<string | null>(null);

    return <div className="themes-list">
        <div className="preview-themes">
        </div>
        {
            themes.map(theme => {
                return <div className="list-item" onClick={() => setSelectingTheme(theme.name)}>
                    { theme.name }
                </div>
            })
        }
        {
            selectingTheme &&
            <PopupConfirm
                title="Confirm"
                text={<div>Are you sure to change the theme to <b>{selectingTheme}</b>? All theme files will be replaced, and any changes you made to the current theme will be lost.</div>}
                name="Change"
                onClick={() => {
                    onSelect(selectingTheme);
                    setSelectingTheme(null)
                }}
                onCancel={() => setSelectingTheme(null)}
            />
        }
    </div>

}