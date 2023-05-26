import React, {useEffect, useState} from "react";
import {
    Popup, PopupBodyDefault,
    PopupFooterSingleButton,
    PopupHeaderDefault
} from "../../ReusableComponents/Popup";

import './appsumo.scss';
import Loader, {FullPageLoader} from "../../ReusableComponents/Loader";
import api from "../../lib/api";
import getSubdomain from "../../logic-helpers/subdomain";
import {toast} from "react-toastify";

export default function AppSumo() {

    const [isOpen, setIsOpen] = useState(false);

    return <div className="appsumo">
        <button
            className="button small"
            onClick={() => setIsOpen(true)}
        >Redeem AppSumo Code</button>
        { isOpen && <AppsumoPopup onClose={() => setIsOpen(false)} /> }
    </div>

}

function AppsumoPopup({onClose} : {onClose: () => any}) {

    const [isLoading, setIsLoading] = useState(true);
    const [codes, setCodes] = useState([] as string[]);

    useEffect(() => {

        api.get<string[]>(getSubdomain(), '/appsumo/codes')
            .then(codes => {
                setCodes(codes);
                setIsLoading(false);
            })

    }, []);

    return <Popup
        header={
            <PopupHeaderDefault title={
                <div>
                    Hey Sumo-lings! <span role="img" aria-label="wave">👋</span>
                </div>
            } />
        }
        body={
            isLoading ?
                <Loader padding={25} /> :
                <PopupBodyDefault>
                    <p>
                        Do you have an AppSumo code? You can enter up to 3 codes per blog to upgrade to a paid plan for free.
                    </p>

                    <div className="codes">

                        <Code plan="starter" codes={codes} />
                        <Code plan="growth" codes={codes} />
                        <Code plan="premium" codes={codes} />

                    </div>
                </PopupBodyDefault>
        }
        footer={
            <PopupFooterSingleButton
                name="Close"
                onClick={onClose}
            />
        }
    />;

}

function Code({plan, codes} : {
    plan: 'starter' | 'growth' | 'premium',
    codes: string[]
}) {

    const isActive =
        plan === 'starter' ||
        (plan === 'growth' && codes.length >= 1) ||
        (plan === 'premium' && codes.length >= 2);

    const hasRedeemed =
        (plan === 'starter' && codes.length >= 1) ||
        (plan === 'growth' && codes.length >= 2) ||
        (plan === 'premium' && codes.length >= 3);


    const [code, setCode] = useState(
        plan === 'starter' ? codes[0] || '' :
            (plan === 'growth' ? codes[1] || '' : codes[2] || '')
    );

    const [isRedeeming, setIsRedeeming] = useState(false);

    function handleRedeem() {

        if (code === '') {
            return toast.error('Please enter a code');
        }

        setIsRedeeming(true);

        api.post(getSubdomain(), '/appsumo/redeem', {
            code
        }).then(() => {
            location.reload();
        }).catch(e => {
            setIsRedeeming(false);
            toast.error(e);
        })

    }

    return <div className={
        "code-row" +
        (!isActive ? " inactive" : "")
    }>
        <span className="plan">{plan}</span>
        <input
            type="text"
            className="input"
            value={code}
            onChange={e => setCode(e.target.value)}
        />
        <button
            className={
                "button small" +
                (hasRedeemed ? " disabled" : "")
            }
            onClick={handleRedeem}
        >Redeem</button>

        { isRedeeming && <FullPageLoader /> }
    </div>;

}