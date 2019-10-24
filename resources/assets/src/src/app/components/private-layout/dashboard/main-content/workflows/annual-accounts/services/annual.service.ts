import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIannualConnection } from './connections/APIannualConnection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../../http/models/stakeholder.model';
import {  IremoveAnnualDoc, Iresubmit, ISubmitCallRecords, IremoveCallShareDoc, ISubmitRegisterOfChargesRecords, ISubmitAnnualAccount, IRemoveOtherDoc, ICheckPreviousFinancialYearRecord, ICompleteForPrivate } from '../models/annualModel';

@Injectable({
  providedIn: 'root'
})
export class AnnualAccountService {

 url: APIannualConnection = new APIannualConnection();

  constructor(private router: Router, private http: HttpClient) { }


  callOnShareData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: ISubmitAnnualAccount): Observable<ISubmitAnnualAccount> {
    return this.http.post<ISubmitAnnualAccount>(this.url.submitCallOnSharesURL(), data);
  }

  removeDoc(data: IremoveCallShareDoc): Observable<IremoveCallShareDoc> {
    return this.http.post<IremoveCallShareDoc>(this.url.removeDocsURL(), data);
  }

  removeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocsURL(), data);
  }

  resubmit(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitURL(), data);
  }

  checkPreviousFinancialYearRecord(data: ICheckPreviousFinancialYearRecord): Observable<ICheckPreviousFinancialYearRecord> {
    return this.http.post<ICheckPreviousFinancialYearRecord>(this.url.checkPreviousYearRecordURL(), data);
  }

  completeForPrivate(data: ICompleteForPrivate): Observable<ICompleteForPrivate> {
    return this.http.post<ICompleteForPrivate>(this.url.completeForPrivateURL(), data);
  }
}

