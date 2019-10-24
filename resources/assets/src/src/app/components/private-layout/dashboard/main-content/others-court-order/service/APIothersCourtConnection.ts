import { APIConnection } from '../../../../../../http/services/connections/APIConnection';


export class APIothersCourtConnection {
    _apiUrl: String;

    url: APIConnection = new APIConnection();

    _callOnSharesData: string;
    _submitCallOnShares: string;
    _uploadForms: string;
    _uploadOther: string;
    _removeDoc: string;
    _removeOtherDoc: string;
    _resubmit: string;
    _uploadOtherResubmitted: string;
    _uploadResubmitted: string;
    _getOtherCourtOrderList: string;
    _getCompanies: string;
    _removeList: string;


    constructor() {

        this._apiUrl = this.url._apiUrl;

        this._callOnSharesData = `${this._apiUrl}/api/v1/other-court-order`;
        this._uploadForms = `${this._apiUrl}/api/v1/others-court-upload-docs`;
        this._uploadOther = `${this._apiUrl}/api/v1/other-court-order-upload-other-docs`;
        this._removeDoc = `${this._apiUrl}/api/v1/other-court-order-remove-docs`;
        this._removeOtherDoc = `${this._apiUrl}/api/v1/other-court-order-remove-other-docs`;
        this._resubmit = `${this._apiUrl}/api/v1/other-court-order-resubmit`;
        this._uploadOtherResubmitted = `${this._apiUrl}/api/v1/other-court-order-resubmitted-other-docs`;
        this._uploadResubmitted = `${this._apiUrl}/api/v1/other-court-order-resubmitted-docs`;
        this._getOtherCourtOrderList = `${this._apiUrl}/api/v1/other-court-order-list`;
        this._getCompanies = `${this._apiUrl}/api/v1/court-order-get-company`;
        this._removeList = `${this._apiUrl}/api/v1/remove-court-order`;
    }

    public getCallOnSharesData() {
        return this._callOnSharesData;
    }
    public submitCallOnSharesURL() {
        return this._submitCallOnShares;
    }
    public uploadDocsURL() {
        return this._uploadForms;
    }
    public removeDocsURL() {
        return this._removeDoc;
    }
    public resubmitURL() {
        return this._resubmit;
    }
    public uploadOtherDocsURL() {
        return this._uploadOther;
    }
    public removeOtherDocsURL() {
        return this._removeOtherDoc;
    }
    public uploadOtherResubmittedDocsURL() {
        return this._uploadOtherResubmitted;
    }
    public uploadResubmittedDocsURL() {
        return this._uploadResubmitted;
    }

    public getCourtOrder() {
        return this._getOtherCourtOrderList;
    }
    public getCompanies() {
        return this._getCompanies;
    }
    public removeList() {
        return this._removeList;
    }
}
