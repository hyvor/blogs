describe('Billing', () => {

  it('shows trial banner', () => {

      cy.clickMainNav('billing');
      cy.contains('30-days trial');

  })

})