export function hasIdArrayChanged<T extends { id: number }>(oldArray: T[], newArray: T[]) {
	return (
		JSON.stringify(oldArray.map((a) => a.id).sort()) !==
		JSON.stringify(newArray.map((a) => a.id).sort())
	);
}
