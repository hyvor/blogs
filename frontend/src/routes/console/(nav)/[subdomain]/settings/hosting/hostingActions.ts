import consoleApi from '../../../../lib/consoleApi';
import type {
	CustomDomainCreateResult,
	CustomDomainSetup,
	CustomDomainTlsProvider,
	HostingInfo
} from '../../../../lib/types';

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

export function createCustomDomainSetup(
	domain: string,
	tlsProvider: CustomDomainTlsProvider = 'auto',
	tlsPrivateKey?: string,
	tlsCertificate?: string
) {
	return consoleApi.post<{
	custom_domain: CustomDomainSetup;
	hosting_info: HostingInfo | null;
	}>({
		endpoint: '/hosting/custom-domain',
		data: {
			domain: domain,
			tls_provider: tlsProvider,
			tls_private_key: tlsPrivateKey,
			tls_certificate: tlsCertificate
		}
	});
}

export function updateCustomDomainSetup(newDomain: string) {
	return consoleApi.patch<CustomDomainSetup>({
		endpoint: '/hosting/custom-domain',
		data: {
			new_domain: newDomain
		}
	});
}

export function updateCustomDomainCerts(tlsPrivateKey: string, tlsCertificate: string) {
	return consoleApi.patch<CustomDomainSetup>({
		endpoint: '/hosting/custom-domain',
		data: {
			tls_private_key: tlsPrivateKey,
			tls_certificate: tlsCertificate
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
