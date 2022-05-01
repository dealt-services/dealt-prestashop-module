window.$(() => {
  const ps = window.prestashop.component;

  const offerGrid = new ps.Grid("dealt_offer");
  offerGrid.addExtension(new ps.GridExtensions.LinkRowActionExtension());
  offerGrid.addExtension(new ps.GridExtensions.SubmitRowActionExtension());
});
