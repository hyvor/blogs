import React, {useState} from "react";
import {Popup, PopupBodyDefault, PopupFooterDoubleButton, PopupHeaderDefault} from "../../ReusableComponents/Popup";
import Input, {InputView} from "../../ReusableComponents/Input";
import {Webhook, WebhookEvent} from "../../types";
import {useWebhooksActions, useWebhooksValues} from "../../logic-helpers/webhooks";
import {WebhookEventNames} from "./Webhooks";

export default function CreateUpdateWebhookPopup(
    { webhook = {} as Webhook, onClose} :
    { webhook?: Webhook, onClose: Function}
) {

    const isCreate = !webhook.id;

    const { create, update } = useWebhooksActions()
    const { createAjax, updateAjax } = useWebhooksValues()

    const [url, setUrl] = useState(webhook.url || '');
    const [events, setEvents] = useState<WebhookEvent[]>(webhook.events || []);

    function handleClick() {
        if (isCreate) {
            create({url, events, onCreate: onClose});
        } else {
            update({id: webhook.id, url, events, onUpdate: onClose});
        }
    }

    return <Popup
        header={<PopupHeaderDefault title="Create Webhook" />}
        body={
            <PopupBodyDefault>
                <div>
                    <Input
                        title="URL"
                        type="text"
                        name="name"
                        value={url}
                        onChange={value => setUrl(value)}
                        placeholder="Webhook URL"
                        autoFocus={true}
                    />
                    <InputView
                        title="Events"
                        content={
                            <EventsSelector
                                events={events}
                                setEvents={setEvents}
                            />
                        }
                    />
                </div>
            </PopupBodyDefault>
        }
        footer={
            <PopupFooterDoubleButton
                onCancel={onClose}
                onClick={handleClick}
                name={isCreate ? 'Create' : 'Update'}
                isLoading={
                    createAjax.status === 'loading' ||
                    updateAjax.status === 'loading'
                }
                loadingName={isCreate ? "Creating" : "Updating"}
            />
        }
    />;

}

function EventsSelector({events, setEvents}: {events: WebhookEvent[], setEvents: (e: WebhookEvent[]) => void}) {

    function handleClick(name: WebhookEvent) {
        if (events.indexOf(name) >= 0) {
            setEvents(events.filter(e => e !== name))
        } else {
            setEvents([...events, name])
        }
    }

    return <div className="events-selector">

        <div className="count">{events.length} event selected</div>

        {
            WebhookEventNames.map(name => {
                return <span
                    className={"event" + (events.indexOf(name) >= 0 ? " selected" : "")}
                    onClick={() => handleClick(name)}
                >{ name }</span>
            })
        }

    </div>

}