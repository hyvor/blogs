import consoleApi from '../../../../lib/consoleApi';
import type { CustomDomainTlsProvider, HostingInfo } from '../../../../lib/types';

export function getHostingInfo() {
	return consoleApi.get<HostingInfo>({
		endpoint: '/hosting'
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

/**
 * Both TLS providers start a pending custom domain intent. "custom" (bring-your-own
 * certificate) starts the hosting change immediately; "auto" needs a follow-up call to
 * verifyCustomDomainSetup() once DNS is pointed at Hyvor Blogs. Re-POSTing with a blog that
 * already has an active custom domain (but no pending intent) reconfigures it - there's no
 * separate "update" endpoint.
 */
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

// aborts a pending (not yet DNS-verified) custom domain intent; active custom domains
// can only be removed by switching hosting back to subdomain
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
