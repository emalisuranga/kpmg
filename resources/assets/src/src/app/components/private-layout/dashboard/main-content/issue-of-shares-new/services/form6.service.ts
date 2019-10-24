import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIform6Connection } from './connections/APIform6Connection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import {  IremoveAnnualDoc, Iresubmit, IremoveCallShareDoc, IRemoveOtherDoc, IupdateCourtDetails, ISubmitCallRecords, ISubmitShareholders, ISubmitNewShareholder, IRemoveShareholder, IRemoveSharClassRecord } from '../models/form6.model';

@Injectable({
  providedIn: 'root'
})
export class Form6Service {

 url: APIform6Connection = new APIform6Connection();

  constructor(private router: Router, private http: HttpClient) { }


  callOnShareData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: ISubmitCallRecords): Observable<ISubmitCallRecords> {
    return this.http.post<ISubmitCallRecords>(this.url.submitCallOnSharesURL(), data);
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

  annualShareholdersSubmit(data: ISubmitShareholders): Observable<ISubmitShareholders> {
    return this.http.post<ISubmitShareholders>(this.url.submitShareholdersURL(), data);
  }
  addNewShareholder(data: ISubmitNewShareholder): Observable<ISubmitNewShareholder> {
    return this.http.post<ISubmitNewShareholder>(this.url.submitNewShareholder(), data);
  }
  addNewExistShareholder(data: ISubmitNewShareholder): Observable<ISubmitNewShareholder> {
    return this.http.post<ISubmitNewShareholder>(this.url.submitExistShareholder(), data);
  }
  removeShareholder(data: IRemoveShareholder): Observable<IRemoveShareholder> {
    return this.http.post<IRemoveShareholder>(this.url.removeShareholder(), data);
  }
  annualNICcheck(data: INICchecker): Observable<INICchecker> {
    return this.http.post<INICchecker>(this.url.getStakeholderNICData(), data);
  }
  removeShareClassRecord(data: IRemoveSharClassRecord): Observable<IRemoveSharClassRecord> {
    return this.http.post<IRemoveSharClassRecord>(this.url.removeShareClassRecord(), data);
  }
}

