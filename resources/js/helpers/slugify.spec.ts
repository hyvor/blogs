import slugify from "./slugify";

test('slugify', function() {

    expect(slugify('Test_1')).toBe('test-1');
    expect(slugify(' Test_1 ')).toBe('test-1');
    expect(slugify(')!@(DN@@!')).toBe('dn');

    // WRITE MORE TESTS IF the function is used
    // CURRENTLY IT IS NOT USED

});