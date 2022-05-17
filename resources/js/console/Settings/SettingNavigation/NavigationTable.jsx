import React, {useState, useEffect} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus, BoxArrowInRight, CodeSlash, Link} from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import navigationLogic from '../../logic/navigationLogic';
import languagesLogic from '../../logic/languagesLogic';
import blogsLogic from '../../logic/blogsLogic';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import DualSetting from '../../ReusableComponents/DualSetting';
import CodemirrorEditor, { CODEMIRROR_MODES } from '../../ReusableComponents/CodemirrorEditor';
import NavigationLanguageSelector from './NavigationLanguageSelector';



export default function Tags ({navigation, subdomain}) 
{}