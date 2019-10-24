import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIRChargesConnection } from './connections/APIChagesConnection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import {  IremoveAnnualDoc, Iresubmit, ISubmitCallRecords, IremoveCallShareDoc, ISubmitRegisterOfChargesRecords, ISubmitChargeRegistrationRecords, ISubmitDeedItems, ISubmitEntitledPersons, IRemoveDeedItem, IRemovePersonItem, IRemoveOtherDoc, IupdateCourtDetails, ICheckAttachedCompany, IAddAttachedCompanies } from '../models/chargesModel';

@Injectable({
  providedIn: 'root'
})
export class RegisterOfChargesService {

 url: APIRChargesConnection = new APIRChargesConnection();

  constructor(private router: Router, private http: HttpClient) { }


  callOnShareData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: ISubmitChargeRegistrationRecords): Observable<ISubmitChargeRegistrationRecords> {
    return this.http.post<ISubmitChargeRegistrationRecords>(this.url.submitCallOnSharesURL(), data);
  }

  removeDoc(data: IremoveCallShareDoc): Observable<IremoveCallShareDoc> {
    return this.http.post<IremoveCallShareDoc>(this.url.removeDocsURL(), data);
  }
  resubmit(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitURL(), data);
  }

  submitDeedItems(data: ISubmitDeedItems): Observable<ISubmitDeedItems> {
    return this.http.post<ISubmitDeedItems>(this.url.submitDeedItems(), data);
  }
  submitEntitledPersons(data: ISubmitEntitledPersons): Observable<ISubmitEntitledPersons> {
    return this.http.post<ISubmitEntitledPersons>(this.url.submitEntitledPersons(), data);
  }
  removeDeedItem(data: IRemoveDeedItem): Observable<IRemoveDeedItem> {
    return this.http.post<IRemoveDeedItem>(this.url.removeDeed(), data);
  }

  removePersonItem(data: IRemovePersonItem): Observable<IRemovePersonItem> {
    return this.http.post<IRemovePersonItem>(this.url.removePerson(), data);
  }

  removeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocsURL(), data);
  }
  updateCourtDetails(data: IupdateCourtDetails): Observable<IupdateCourtDetails> {
    return this.http.post<IupdateCourtDetails>(this.url.updateCourtURL(), data);
  }

  checkAttachedCompanies(data: ICheckAttachedCompany): Observable<ICheckAttachedCompany> {
    return this.http.post<ICheckAttachedCompany>(this.url.checkAttachedCompanies(), data);
  }

  addCompanies(data: IAddAttachedCompanies): Observable<IAddAttachedCompanies> {
    return this.http.post<IAddAttachedCompanies>(this.url.addCompanies(), data);
  }

}

