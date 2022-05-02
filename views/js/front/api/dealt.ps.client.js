export class DealtPSClient {
  offerAvailability(offerId, zipCode) {
    return $.post(DealtGlobals.actions.api, {
      action: "offerAvailability",
      id_dealt_offer: offerId,
      zip_code: zipCode,
    });
  }
}
