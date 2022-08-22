import byteFormatter from "./byteFormatter";
import { expect } from '@jest/globals';

test('byte formatter', function() {

    expect(byteFormatter(0)).toBe('0 Bytes');
    expect(byteFormatter(100)).toBe('100 B');
    expect(byteFormatter(1000)).toBe('1 KB');
    expect(byteFormatter(2500)).toBe('3 KB');
    expect(byteFormatter(3000000)).toBe('3 MB');
    expect(byteFormatter(6000000000)).toBe('6 GB');
    expect(byteFormatter(2000000000000)).toBe('2 TB');

});