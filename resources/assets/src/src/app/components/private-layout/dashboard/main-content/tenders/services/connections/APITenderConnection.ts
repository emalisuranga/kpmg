import { APIConnection } from '../../../../../../../http/services/connections/APIConnection';
export class APITenderConnection {
  _apiUrl: String;
  _getPublications: string;
  _getUserTenders: string;
  _getTender: string;
  _getTenderAdd: string;
  _getTenderItemAdd: string;
  _getTenders: string;

  _getUserApplications: string;

  _applyTender: string;
  _tenderFileUpload: string;
  _tenderOtherFileUpload: string;
  _tenderOtherResubmittedFileUpload: string;
  _tenderFileRemove: string;
  _tenderOtherFileRemove: string;
  _tenderPay: string;
  _tenderDocUpload: string;
  _tenderDocRemove: string;

  _getResubmitTender: string;
  _resubmitTender: string;
  _tenderResubmitPay: string;

  _tenderAwordByPublisher: string;
  _getAwordingTender: string;
  _getAwordFileUpload: string;
  _getAwordOtherFileUpload: string;
  _getAwordorOtherResubmittedFileUpload: string;
  _tenderAwordFileRemove: string;
  _tenderAwordOtherFileRemove: string;
  _tenderAwarded: string;
  _tenderAwardResubmitted: string;
  _getCloseTender: string;
  _updateContractDetails: string;
  _updateAwardSigningPartyDetails: string;

  _getRenewalTender: string;
  _tenderRenwalFileUpload: string;
  _tenderRenwalFileRemove: string;
  _getRenewalResubmissionTender: string;

  _getReRegTender: string;
  _tenderReRegFileUpload: string;
  _tenderReRegFileRemove: string;
  _getReRegResubmissionTender: string;

  _updatePCA7details: string;
  _tenderRenewalResubmitted: string;
  _tenderReRegResubmitted: string;

  _tenderCheckAlreadyApplied: string;
  _tenderChangeClosingDatePublisher: string;

  _tenderPublisherOtherFileUpload: string;
  _tenderPublihserOtherFileUpload: string;

  _tenderRenewalReregNewRecord: string;

  url: APIConnection = new APIConnection();

  constructor() {

    this._apiUrl = this.url._apiUrl;
    this._getPublications =  `${this._apiUrl}/api/v1/get-publications`;
    this._tenderDocUpload = `${this._apiUrl}/api/v1/tender-document-upload`;
    this._tenderDocRemove = `${this._apiUrl}/api/v1/tender-document-remove`;
    this._getUserTenders = `${this._apiUrl}/api/v1/get-user-tenders`;
    this._getTender = `${this._apiUrl}/api/v1/get-tender`;
    this._getTenderAdd = `${this._apiUrl}/api/v1/add-tender`;
    this._getTenderItemAdd = `${this._apiUrl}/api/v1/add-tender-items`;
    this._getTenders = `${this._apiUrl}/api/v1/get-tenders`;

    this._getUserApplications = `${this._apiUrl}/api/v1/tender-user-applications`;

    this._getCloseTender = `${this._apiUrl}/api/v1/get-close-tender`;
    this._applyTender = `${this._apiUrl}/api/v1/apply-tender`;
    this._tenderFileUpload = `${this._apiUrl}/api/v1/tender-file-upload`;
    this._tenderOtherFileUpload = `${this._apiUrl}/api/v1/tender-other-file-upload`;
    this._tenderOtherResubmittedFileUpload = `${this._apiUrl}/api/v1/tender-file-upload_resubmit_other`;
    this._tenderFileRemove = `${this._apiUrl}/api/v1/tender-file-remove`;
    this._tenderOtherFileRemove = `${this._apiUrl}/api/v1/tender-other-file-remove`;
    this._tenderPay = `${this._apiUrl}/api/v1/tender-apply-pay`;

    this._getResubmitTender = `${this._apiUrl}/api/v1/get-resubmitted-tender`;
    this._resubmitTender = `${this._apiUrl}/api/v1/resubmit-tender`;
    this._tenderResubmitPay = `${this._apiUrl}/api/v1/tender-resubmitted`;

    this._tenderAwordByPublisher = `${this._apiUrl}/api/v1/tender-aword-by-publisher`;
    this._tenderChangeClosingDatePublisher = `${this._apiUrl}/api/v1/tender-change-closing-date-publisher`;
    this._getAwordingTender = `${this._apiUrl}/api/v1/get-awording-tender`;
    this._getAwordFileUpload =  `${this._apiUrl}/api/v1/tender-aword-file-upload`;
    this._getAwordOtherFileUpload = `${this._apiUrl}/api/v1/tender-aword-other-file-upload`;
    this._getAwordorOtherResubmittedFileUpload = `${this._apiUrl}/api/v1/tender-aword-upload-resubmit-other`;
    this._tenderAwordFileRemove = `${this._apiUrl}/api/v1/tender-aword-file-remove`;
    this._tenderAwordOtherFileRemove = `${this._apiUrl}/api/v1/tender-aword-other-file-remove`;
    this._tenderAwarded = `${this._apiUrl}/api/v1/tender-awarded`;
    this._tenderAwardResubmitted = `${this._apiUrl}/api/v1/tender-award-resubmitted`;
    this._updateContractDetails =  `${this._apiUrl}/api/v1/tender-upate-contact-details`;
    this._updateAwardSigningPartyDetails = `${this._apiUrl}/api/v1/tender-aword-signing-party-detail`;

    this._getRenewalTender = `${this._apiUrl}/api/v1/renewal-tender`;
    this._tenderRenwalFileUpload = `${this._apiUrl}/api/v1/renewal-tender-file-upload`;
    this._tenderRenwalFileRemove = `${this._apiUrl}/api/v1/renewal-tender-file-remove`;
    this._getRenewalResubmissionTender = `${this._apiUrl}/api/v1/renewal-resubmission-tender`;

    this._getReRegTender = `${this._apiUrl}/api/v1/rereg-tender`;
    this._tenderReRegFileUpload = `${this._apiUrl}/api/v1/rereg-tender-file-upload`;
    this._tenderReRegFileRemove = `${this._apiUrl}/api/v1/rereg-tender-file-remove`;
    this._getReRegResubmissionTender = `${this._apiUrl}/api/v1/rereg-resubmission-tender`;

    this._updatePCA7details =  `${this._apiUrl}/api/v1/renewal-rereg-tender-update-pca7`;
    this._tenderRenewalResubmitted =  `${this._apiUrl}/api/v1/renewal-resubmitted`;
    this._tenderReRegResubmitted =  `${this._apiUrl}/api/v1/rereg-resubmitted`;

    this._tenderCheckAlreadyApplied =  `${this._apiUrl}/api/v1/check-already-applied-items`;

    this._tenderPublisherOtherFileUpload = `${this._apiUrl}/api/v1/tender-publisher-other-file-upload`;
    this._tenderPublihserOtherFileUpload = `${this._apiUrl}/api/v1/tender-publihser-other-file-remove`;

    this._tenderRenewalReregNewRecord = `${this._apiUrl}/api/v1/rereg-new-record`;

  }

  public getPublicationsURL() {
    return this._getPublications;
  }

  public getUserTendersURL() {
    return this._getUserTenders;
  }

  public getUserApplicationsURL() {
    return this._getUserApplications;
  }

  public getCloseTender() {
    return this._getCloseTender;
  }

  public getTenderURL() {
    return this._getTender;
  }

  public getTenderAddURL() {
    return this._getTenderAdd;
  }
  public getTenderItemAddURL() {
    return this._getTenderItemAdd;
  }

  public getTendersInfo() {
    return this._getTenders;
  }

  public applyTenderURL() {
    return this._applyTender;
  }

  public getFileUploadURL() {
    return this._tenderFileUpload;
  }
  public getOtherFileUploadURL() {
    return this._tenderOtherFileUpload;
  }

  public getResubmittedFileUploadURL() {
    return this._tenderOtherResubmittedFileUpload;
  }
  public removeFileUploadURL() {
    return this._tenderFileRemove;
  }
  public removeOtherFileUploadURL() {
    return this._tenderOtherFileRemove;
  }

  public tenderApplyPayURL() {
    return this._tenderPay;
  }

  public getResubmitTenderURL() {
    return this._getResubmitTender;
  }

  public resubmitTenderURL() {
    return this._resubmitTender;
  }

  public tenderResubmitPayURL() {
    return this._tenderResubmitPay;
  }

  public tenderAwordByPubliserURL() {
    return this._tenderAwordByPublisher;
  }

  public tenderAwrodingURL() {
   return this._getAwordingTender;
  }

  public getAwordFileUploadURL() {
    return this._getAwordFileUpload;
  }
  public getAwordResubmittedFileUploadURL() {
    return this._getAwordorOtherResubmittedFileUpload;
  }
  public getAwardOtherFileUploadURL() {
    return this._getAwordOtherFileUpload;
  }

  public removeAwordingFileUploadURL() {
    return this._tenderAwordFileRemove;
  }
  public removeAwordingOtherFileUploadURL() {
    return this._tenderAwordOtherFileRemove;
  }
  public tenderAwardedUrl() {
    return this._tenderAwarded;
  }

  public tenderAwardResubmittedUrl() {
    return this._tenderAwardResubmitted;
  }

  public getUpdateContractDetailsURL() {
    return this._updateContractDetails;
  }
  public getAwardingSigningPartyDetailsURL(){
    return this._updateAwardSigningPartyDetails;
  }

  public getTendeDocUploadURL() {
    return this._tenderDocUpload;
  }

  public getTenderDocRemoveURL() {
    return this._tenderDocRemove;
  }

  public getRnewalTenderURL() {
    return this._getRenewalTender;
  }

  public geteRenewalUploadURL() {
    return this._tenderRenwalFileUpload;
  }

  public removeRenwalFileUploadURL() {
    return this._tenderRenwalFileRemove;
  }

  public getRnewalResubmissionTenderURL() {
    return this._getRenewalResubmissionTender;
  }


  public getReRegTenderURL() {
    return this._getReRegTender;
  }

  public geteReRegUploadURL() {
    return this._tenderReRegFileUpload;
  }

  public removeReRegFileUploadURL() {
    return this._tenderReRegFileRemove;
  }

  public getReRegResubmissionTenderURL() {
    return this._getReRegResubmissionTender;
  }

  public getPCA7updateDetailsURL(){
    return this._updatePCA7details;
  }

  public getTenderRenewalReusmittedURL(){
    return this._tenderRenewalResubmitted;
  }

  public getTenderReRegReusmittedURL(){
    return this._tenderReRegResubmitted;
  }

  public getCheckAlreadyAppliedTenderURL() {
    return this._tenderCheckAlreadyApplied;
  }

  public getChangeItemCloseDateURL() {
    return this._tenderChangeClosingDatePublisher;
  }

  public getPublisherOtherFileUploadURL() {
    return this._tenderPublisherOtherFileUpload;
  }

  public tenderPublihserOtherFileUpload() {
    return this._tenderPublihserOtherFileUpload;
  }

  public tenderRenewalReregNewRecord() {
    return this._tenderRenewalReregNewRecord;
  }


}
