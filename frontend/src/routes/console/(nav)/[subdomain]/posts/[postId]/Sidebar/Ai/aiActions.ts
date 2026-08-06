import { getConsoleBlogBaseUrl } from '../../../../../../lib/consoleApi';
import { get } from 'svelte/store';
import { authOrganizationStore } from '../../../../../../lib/stores';

export async function callAgent() {
	
	const response = await fetch(getConsoleBlogBaseUrl() + '/ai/agent', {
		method: 'POST',
		headers: { 
			'Content-Type': 'application/json',
			'X-Organization-Id': String(get(authOrganizationStore)?.id)
		},
		body: JSON.stringify({}),
		credentials: 'same-origin',
	});

	const reader = response.body.getReader();
	const decoder = new TextDecoder();
	let buffer = '';

	while (true) {
		console.log('Reading from stream...');
		const { done, value } = await reader.read();
		if (done) break;

		buffer += decoder.decode(value, { stream: true });
		console.log('Buffer:', buffer);

		// SSE events are separated by blank lines
		const parts = buffer.split('\n\n');
		buffer = parts.pop(); // keep incomplete chunk for next read

		console.log('Processing parts:', parts);

		for (const part of parts) {
			const line = part.replace(/^data: /, '').trim();
			if (!line) continue;
			console.log(line);

			// const event = JSON.parse(line);
			// onEvent(event);
		}
	}

}

// export function sendPrompt(prompt: string, post_id: number) {
// 	return consoleApi.post<GptPrompt>({
// 		endpoint: '/gpt/prompt',
// 		data: {
// 			post_id: post_id,
// 			prompt: prompt
// 		}
// 	});
// }

// export function getPrompts(post_id: number) {
// 	return consoleApi.get<GptPrompt[]>({
// 		endpoint: '/gpt/post-history',
// 		data: {
// 			post_id
// 		}
// 	});
// }

// export function resetChat(post_id: number) {
// 	return consoleApi.delete({
// 		endpoint: '/gpt/post-history',
// 		data: {
// 			post_id
// 		}
// 	});
// }
