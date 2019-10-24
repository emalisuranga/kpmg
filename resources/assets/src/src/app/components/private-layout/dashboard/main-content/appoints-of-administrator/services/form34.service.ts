import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIForm34Connection } from './connections/APIForm34Connection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import {  IremoveAnnualDoc, Iresubmit, ISubmitCallRecords, IremoveCallShareDoc, ISubmitAdministrators, IRemoveOtherDoc, IupdateCourtDetails, IAddAdminCompanies } from '../models/form34.model';
import { ICheckAdminCompany } from '../../../../../../http/models/general.model';

@Injectable({
  providedIn: 'root'
})
export class Form34Service {

 url: APIForm34Connection = new APIForm34Connection();

  constructor(private router: Router, private http: HttpClient) { }


  callOnShareData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: ISubmitAdministrators): Observable<ISubmitAdministrators> {
    return this.http.post<ISubmitAdministrators>(this.url.submitCallOnSharesURL(), data);
  }

  removeDoc(data: IremoveCallShareDoc): Observable<IremoveCallShareDoc> {
    return this.http.post<IremoveCallShareDoc>(this.url.removeDocsURL(), data);
  }
  resubmit(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitURL(), data);
  }

  removeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocsURL(), data);
  }
  updateCourtDetails(data: IupdateCourtDetails): Observable<IupdateCourtDetails> {
    return this.http.post<IupdateCourtDetails>(this.url.updateCourtURL(), data);
  }

  checkAdminCompanies(data: ICheckAdminCompany): Observable<ICheckAdminCompany> {
    return this.http.post<ICheckAdminCompany>(this.url.checkAdminCompanies(), data);
  }

  addAdminCompanies(data: IAddAdminCompanies): Observable<IAddAdminCompanies> {
    return this.http.post<IAddAdminCompanies>(this.url.addAdminCompanies(), data);
  }
}

