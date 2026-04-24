import consoleApi from '../../../../lib/consoleApi';
import type { CustomDomainHosting } from '../../../../lib/types';

export function getCustomDomainHosting() {
	return consoleApi.get<CustomDomainHosting>({
		endpoint: '/custom-domain',
		v1: true
	});
}
