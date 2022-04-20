window.$(() => {
  const ps = window.prestashop.component;

  const missionsGrid = new ps.Grid("dealt_missions");
  missionsGrid.addExtension(new ps.GridExtensions.LinkRowActionExtension());
});
