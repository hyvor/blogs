import consoleApi from "../../../../lib/consoleApi";


export function applyDocumentChanges(
    eventId: number,
    finalContent: string,
    agentVersion: number,
    force: boolean
) {

    return consoleApi.post({
        endpoint: '/ai/document-changes/apply',
        data: {
            event_id: eventId,
            content: finalContent,
            agent_version: agentVersion,
            force: force
        }
    })

}