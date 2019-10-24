import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APITenderConnection } from './connections/APITenderConnection';

// tslint:disable-next-line:max-line-length
import { ITender, ISubmitTenderItems, IGetTender, IGetUserTenderList, IGetTenders, IapplyTender, IapplyTenderSubmit, IremoveTenderDoc, ItenderApplyPay, IGetResubmitTender, IGetPublications, ItenderAwordByPublisher, IGetAwordingTender, IremoveTenderAwardingDoc, IAwordTender, IUpdateContractDetails, IremoveTenderSpecificDoc, IGetRenewalTender, IremoveTenderRenwalDoc, IupdatePCA7, IRenwalReRegResubmit, ICheckAlreadyAppliedSubmit, ISubmitAwardingSigningParty, IchangeItemCloseDateByPublisher, IRemoveOtherDoc, ICreateNewRenewalReRegRecord } from '../models/tender.model';

@Injectable({
  providedIn: 'root'
})
export class TenderService {

  url: APITenderConnection = new APITenderConnection();

  constructor(private router: Router, private http: HttpClient) { }

  publicationsGet(data: IGetPublications): Observable<IGetPublications> {
    return this.http.post<IGetPublications>(this.url.getPublicationsURL(), data);
  }

  userTendersGet(data: IGetUserTenderList): Observable<IGetUserTenderList> {
    return this.http.post<IGetUserTenderList>(this.url.getUserTendersURL(), data);
  }

  tenderGet(data: IGetTender): Observable<IGetTender> {
    return this.http.post<IGetTender>(this.url.getTenderURL(), data);
  }

  tenderAdd(data: ITender): Observable<ITender> {
    return this.http.post<ITender>(this.url.getTenderAddURL(), data);
  }
  tenderAddItems(data: ISubmitTenderItems): Observable<ISubmitTenderItems> {
    return this.http.post<ISubmitTenderItems>(this.url.getTenderItemAddURL(), data);
  }

  getTenders(data: IGetTenders): Observable<IGetTenders> {
    return this.http.post<IGetTenders>(this.url.getTendersInfo(), data);
  }

  getUserApplications(data: null): Observable<any> {
    return this.http.post<any>(this.url.getUserApplicationsURL(), data);
  }


  getCloseTender(data: IGetTenders): Observable<IGetTenders> {
    return this.http.post<IGetTenders>(this.url.getCloseTender(), data);
  }

  applyTender(data: IapplyTenderSubmit): Observable<IapplyTenderSubmit> {
    return this.http.post<IapplyTenderSubmit>(this.url.applyTenderURL(), data);
  }

  removeTenderDoc(data: IremoveTenderDoc): Observable<IremoveTenderDoc> {
    return this.http.post<IremoveTenderDoc>(this.url.removeFileUploadURL(), data);
  }
  removeTenderOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherFileUploadURL(), data);
  }

  tenderApplyPay(data: ItenderApplyPay): Observable<ItenderApplyPay> {
    return this.http.post<ItenderApplyPay>(this.url.tenderApplyPayURL(), data);
  }

  tenderResubmitGet(data: IGetResubmitTender): Observable<IGetResubmitTender> {
    return this.http.post<IGetResubmitTender>(this.url.getResubmitTenderURL(), data);
  }

  resubmitTender(data: IapplyTenderSubmit): Observable<IapplyTenderSubmit> {
    return this.http.post<IapplyTenderSubmit>(this.url.resubmitTenderURL(), data);
  }

tenderResubmitPay(data: ItenderApplyPay): Observable<ItenderApplyPay> {
    return this.http.post<ItenderApplyPay>(this.url.tenderResubmitPayURL(), data);
  }

  tenderAwordByPublisher(data: ItenderAwordByPublisher): Observable<ItenderAwordByPublisher> {
    return this.http.post<ItenderAwordByPublisher>(this.url.tenderAwordByPubliserURL(), data);
  }

  tenderAwordingGet(data: IGetAwordingTender): Observable<IGetAwordingTender> {
    return this.http.post<IGetAwordingTender>(this.url.tenderAwrodingURL(), data);
  }

  removeTenderAwardingDoc(data: IremoveTenderAwardingDoc): Observable<IremoveTenderAwardingDoc> {
    return this.http.post<IremoveTenderAwardingDoc>(this.url.removeAwordingFileUploadURL(), data);
  }

  removeTenderAwardingOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeAwordingOtherFileUploadURL(), data);
  }


  tenderAwarded(data: IAwordTender): Observable<IAwordTender> {
    return this.http.post<IAwordTender>(this.url.tenderAwardedUrl(), data);
  }

  tenderAwardingResubmitted(data: IAwordTender): Observable<IAwordTender> {
    return this.http.post<IAwordTender>(this.url.tenderAwardResubmittedUrl(), data);
  }

  updateContract(data: IUpdateContractDetails): Observable<IUpdateContractDetails> {
    return this.http.post<IUpdateContractDetails>(this.url.getUpdateContractDetailsURL(), data);
  }

  updateAwardSigningParty(data: ISubmitAwardingSigningParty): Observable<ISubmitAwardingSigningParty> {
    return this.http.post<ISubmitAwardingSigningParty>(this.url.getAwardingSigningPartyDetailsURL(), data);
  }


  removeTenderSepecificDoc(data: IremoveTenderSpecificDoc): Observable<IremoveTenderSpecificDoc> {
    return this.http.post<IremoveTenderSpecificDoc>(this.url.getTenderDocRemoveURL(), data);
  }


  tenderRenwalGet(data: IGetRenewalTender): Observable<IGetRenewalTender> {
    return this.http.post<IGetRenewalTender>(this.url.getRnewalTenderURL(), data);
  }

  removeTenderRenwalDoc(data: IremoveTenderRenwalDoc): Observable<IremoveTenderRenwalDoc> {
    return this.http.post<IremoveTenderRenwalDoc>(this.url.removeRenwalFileUploadURL(), data);
  }

  tenderRenwalResubmissionGet(data: IGetRenewalTender): Observable<IGetRenewalTender> {
    return this.http.post<IGetRenewalTender>(this.url.getRnewalResubmissionTenderURL(), data);
  }


  tenderReRegGet(data: IGetRenewalTender): Observable<IGetRenewalTender> {
    return this.http.post<IGetRenewalTender>(this.url.getReRegTenderURL(), data);
  }

  removeTenderReRegDoc(data: IremoveTenderRenwalDoc): Observable<IremoveTenderRenwalDoc> {
    return this.http.post<IremoveTenderRenwalDoc>(this.url.removeReRegFileUploadURL(), data);
  }

  tenderReRegResubmissionGet(data: IGetRenewalTender): Observable<IGetRenewalTender> {
    return this.http.post<IGetRenewalTender>(this.url.getReRegResubmissionTenderURL(), data);
  }

  tenderPCA7update(data: IupdatePCA7): Observable<IupdatePCA7> {
    return this.http.post<IupdatePCA7>(this.url.getPCA7updateDetailsURL(), data);
  }
  tenderRenewalResubmit(data: IRenwalReRegResubmit): Observable<IRenwalReRegResubmit> {
    return this.http.post<IRenwalReRegResubmit>(this.url.getTenderRenewalReusmittedURL(), data);
  }

  tenderReRegResubmit(data: IRenwalReRegResubmit): Observable<IRenwalReRegResubmit> {
    return this.http.post<IRenwalReRegResubmit>(this.url.getTenderReRegReusmittedURL(), data);
  }

  tenderCheckAlreadyApplied(data: ICheckAlreadyAppliedSubmit): Observable<ICheckAlreadyAppliedSubmit> {
    return this.http.post<ICheckAlreadyAppliedSubmit>(this.url.getCheckAlreadyAppliedTenderURL(), data);
  }

  changeItemCloseDateByPublisher(data: IchangeItemCloseDateByPublisher): Observable<IchangeItemCloseDateByPublisher> {
    return this.http.post<IchangeItemCloseDateByPublisher>(this.url.getChangeItemCloseDateURL(), data);
  }

  removeTenderPublisherOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.tenderPublihserOtherFileUpload(), data);
  }


   tenderRenewalReregNewRecord(data: ICreateNewRenewalReRegRecord): Observable<ICreateNewRenewalReRegRecord> {
    return this.http.post<ICreateNewRenewalReRegRecord>(this.url.tenderRenewalReregNewRecord(), data);
  }

}

