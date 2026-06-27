import consoleApi from '../../../../lib/consoleApi';
import type { CustomDomainSetup, HostingInfo } from '../../../../lib/types';

export function getHostingInfo() {
	return consoleApi.get<HostingInfo>({
		endpoint: '/hosting'
	});
}

export function updateHostedAt(hostingAt: 'subdomain' | 'self', hostingUrl?: string) {
	const data: Record<string, string> = {
		hosting_at: hostingAt
	};

	if (hostingAt === 'self') {
		if (!hostingUrl) {
			throw new Error('Hosting URL is required when self-hosting');
		}
		data['hosting_url'] = hostingUrl;
	}

	return consoleApi.post<HostingInfo>({
		endpoint: '/hosting',
		data: data
	});
}

export function createCustomDomainSetup(domain: string) {
	return consoleApi.post<CustomDomainSetup>({
		endpoint: '/hosting/custom-domain',
		data: {
			domain: domain
		}
	});
}

export function updateCustomDomainSetup(oldDomain: string, newDomain: string) {
	return consoleApi.patch<CustomDomainSetup>({
		endpoint: '/hosting/custom-domain',
		data: {
			old_domain: oldDomain,
			new_domain: newDomain
		}
	});
}

export function deleteCustomDomainSetup() {
	return consoleApi.delete<void>({
		endpoint: '/hosting/custom-domain'
	});
}

export function verifyCustomDomainSetup() {
	return consoleApi.post<CustomDomainSetup>({
		endpoint: '/hosting/custom-domain/verify'
	});
}

export function getCustomDomainHosting() {
	return consoleApi.get<CustomDomainSetup>({
		endpoint: '/custom-domain',
		v1: true
	});
}
