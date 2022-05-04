export class DealtPSClient {
  offerAvailability(offerId, zipCode) {
    return new Promise((resolve, reject) =>
      $.post(DealtGlobals.actions.api, {
        action: "offerAvailability",
        dealt_id_offer: offerId,
        zip_code: zipCode,
      })
        .then(resolve)
        .catch(reject)
    );
  }
}
