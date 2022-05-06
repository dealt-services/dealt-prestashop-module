window.$(() => {
  const ps = window.prestashop.component;

  const missionGrid = new ps.Grid("dealt_mission");
  missionGrid.addExtension(new ps.GridExtensions.FiltersResetExtension());
});
