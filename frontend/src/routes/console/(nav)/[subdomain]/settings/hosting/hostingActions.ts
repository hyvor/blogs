import consoleApi from '../../../../lib/consoleApi';
import type { CustomDomainTlsProvider, HostingChange, HostingInfo } from '../../../../lib/types';

export function getHostingInfo() {
	return consoleApi.get<HostingInfo>({
		endpoint: '/hosting'
	});
}

export function getHostingHistory(limit: number = 20, offset: number = 0) {
	return consoleApi.get<HostingChange[]>({
		endpoint: '/hosting/history',
		data: { limit, offset }
	});
}

export function updateHostedAt(
	hostingAt: 'subdomain' | 'self',
	hostingUrl?: string,
	subdomain?: string
) {
	const data: Record<string, string> = {
		hosting_at: hostingAt
	};

	if (hostingAt === 'self') {
		if (!hostingUrl) {
			throw new Error('Hosting URL is required when self-hosting');
		}
		data['hosting_url'] = hostingUrl;
	}

	if (subdomain) {
		data['subdomain'] = subdomain;
	}

	return consoleApi.post<HostingInfo>({
		endpoint: '/hosting',
		data: data
	});
}

export function createCustomDomainSetup(
	domain: string,
	tlsProvider: CustomDomainTlsProvider = 'auto',
	tlsPrivateKey?: string,
	tlsCertificate?: string
) {
	return consoleApi.post<HostingInfo>({
		endpoint: '/hosting/custom-domain',
		data: {
			domain: domain,
			tls_provider: tlsProvider,
			tls_private_key: tlsPrivateKey,
			tls_certificate: tlsCertificate
		}
	});
}

export function deleteCustomDomainIntent() {
	return consoleApi.delete<void>({
		endpoint: '/hosting/custom-domain'
	});
}

export function verifyCustomDomainSetup() {
	return consoleApi.post<HostingInfo>({
		endpoint: '/hosting/custom-domain/verify'
	});
}
