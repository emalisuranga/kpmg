import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIForm37Connection } from './connections/APIForm37Connection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import {  IremoveAnnualDoc, Iresubmit, ISubmitCallRecords, IremoveCallShareDoc, ISubmitRegisterOfChargesRecords, ISubmitChargeRegistrationRecords, ISubmitNameChangeOverseasRecords, IupdateCourtDetails } from '../models/form37Model';

@Injectable({
  providedIn: 'root'
})
export class Form37Service {

 url: APIForm37Connection = new APIForm37Connection();

  constructor(private router: Router, private http: HttpClient) { }


  callOnShareData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getCallOnSharesData(), data);
  }

  shareCallSubmit(data: ISubmitNameChangeOverseasRecords): Observable<ISubmitNameChangeOverseasRecords> {
    return this.http.post<ISubmitNameChangeOverseasRecords>(this.url.submitCallOnSharesURL(), data);
  }

  removeDoc(data: IremoveCallShareDoc): Observable<IremoveCallShareDoc> {
    return this.http.post<IremoveCallShareDoc>(this.url.removeDocsURL(), data);
  }
  resubmit(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitURL(), data);
  }

  removeOtherDoc(data: any): Observable<any> {
    return this.http.post(this.url.removeOtherDocsURL(), data);
  }

  updateCourtDetails(data: IupdateCourtDetails): Observable<IupdateCourtDetails> {
    return this.http.post<IupdateCourtDetails>(this.url.updateCourtURL(), data);
  }
}

