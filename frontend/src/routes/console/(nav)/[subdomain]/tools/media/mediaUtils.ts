export function toKebabCase(str: string | null): string {
	if (!str) {
		return '';
	}
	return str
		.replace(/\s+/g, '-') // Replace spaces with hyphens
		.replace(/[A-Z]/g, (letter) => `-${letter.toLowerCase()}`) // Add hyphen before capital letters and convert them to lowercase
		.replace(/_+/g, '-') // Replace underscores with hyphens
		.replace(/--+/g, '-') // Replace multiple hyphens with a single one
		.replace(/^-|-$|^-+|-+$/g, '') // Remove leading and trailing hyphens
		.toLowerCase(); // Ensure everything is in lowercase
}
