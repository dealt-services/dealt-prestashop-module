export class DealtPSClient {
  offerAvailability(offerId, zipCode) {
    return new Promise((resolve, reject) =>
      $.post(DealtGlobals.actions.api, {
        action: "offerAvailability",
        id_dealt_offer: offerId,
        zip_code: zipCode,
      })
        .then(resolve)
        .catch(reject)
    );
  }
}
