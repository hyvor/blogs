import consoleApi from '../../../../lib/consoleApi';
import type { CustomDomainTlsProvider, HostingInfo } from '../../../../lib/types';

/**
 * The hosting URL is updated with the blog update endpoint (updateBlog). The subdomain cannot be changed.
 * Custom domains are managed with the endpoints below.
 */

export function getHostingInfo() {
	return consoleApi.get<HostingInfo>({
		endpoint: '/hosting'
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

export function verifyCustomDomainSetup() {
	return consoleApi.post<HostingInfo>({
		endpoint: '/hosting/custom-domain/verify'
	});
}

export function deleteCustomDomainIntent() {
	return consoleApi.delete<HostingInfo>({
		endpoint: '/hosting/custom-domain/intent'
	});
}

export function deleteCustomDomain() {
	return consoleApi.delete<HostingInfo>({
		endpoint: '/hosting/custom-domain'
	});
}
