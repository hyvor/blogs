import consoleApi from '../../../../lib/consoleApi';
import type {
	CustomDomainIntent,
	CustomDomainSetup,
	CustomDomainTlsProvider,
	HostingInfo
} from '../../../../lib/types';

interface CustomDomainMutationResult {
	custom_domain: CustomDomainSetup | null;
	custom_domain_intent: CustomDomainIntent | null;
	hosting_info: HostingInfo | null;
}

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
	return consoleApi.post<CustomDomainMutationResult>({
		endpoint: '/hosting/custom-domain',
		data: {
			domain: domain,
			tls_provider: tlsProvider,
			tls_private_key: tlsPrivateKey,
			tls_certificate: tlsCertificate
		}
	});
}

/**
 * Reconfigures the blog's custom domain (or pending intent): the domain name, the TLS
 * provider, or both. Switching to (or staying on) "custom" requires a private key + certificate;
 * switching to "auto" only (re-)creates a pending intent that then needs DNS verification.
 */
export function updateCustomDomain(params: {
	newDomain?: string;
	tlsProvider?: CustomDomainTlsProvider;
	tlsPrivateKey?: string;
	tlsCertificate?: string;
}) {
	return consoleApi.patch<CustomDomainMutationResult>({
		endpoint: '/hosting/custom-domain',
		data: {
			new_domain: params.newDomain,
			tls_provider: params.tlsProvider,
			tls_private_key: params.tlsPrivateKey,
			tls_certificate: params.tlsCertificate
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
	return consoleApi.post<{
		custom_domain: CustomDomainSetup;
		hosting_info: HostingInfo;
	}>({
		endpoint: '/hosting/custom-domain/verify'
	});
}
