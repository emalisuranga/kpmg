import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { APIForm23Connection } from './connections/APIForm23Connection';
import { IIncorporationData, IIncorporationDataStep1Data } from '../../../../../../http/models/incorporation.model';
import { INICchecker } from '../../../../../../http/models/stakeholder.model';
import { ISubmitDirectors, ISubmitSecretories, ISubmitShareholders, ISubmitShareRegisters, ISubmitAnnualRecords, ISubmitAnnualAuditors, ISubmitAnnualCharges, ISubmitShareRecords, IremoveAnnualDoc, Iresubmit, IRemoveExistingDirector, IUpdateExistingDirector, IAddNewDirector, IRemoveExistingSec, IUpdateExistingSec, IAddNewSec, IUpdateOtherDocDate, IUpdateAlterType, IRemoveOtherDoc, IupdateCourtDetails } from '../models/form23.model';

@Injectable({
  providedIn: 'root'
})
export class Form23Service {

 url: APIForm23Connection = new APIForm23Connection();

  constructor(private router: Router, private http: HttpClient) { }

  annualData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getAnnualData(), data);
  }
  annualHeavyData(data: IIncorporationData): Observable<IIncorporationData> {
    return this.http.post<IIncorporationData>(this.url.getAnnualHeavyData(), data);
  }
  annualNICcheck(data: INICchecker): Observable<INICchecker> {
    return this.http.post<INICchecker>(this.url.getStakeholderNICData(), data);
  }
  annualStep1Submit(data: IIncorporationDataStep1Data): Observable<IIncorporationDataStep1Data> {
    return this.http.post<IIncorporationDataStep1Data>(this.url.submitStep1URL(), data);
  }
  annualDirectorsSubmit(data: ISubmitDirectors): Observable<ISubmitDirectors> {
    return this.http.post<ISubmitDirectors>(this.url.submitDirectorsURL(), data);
  }
  annualSecretoriesSubmit(data: ISubmitSecretories): Observable<ISubmitSecretories> {
    return this.http.post<ISubmitSecretories>(this.url.submitSecretoriesURL(), data);
  }
  annualShareholdersSubmit(data: ISubmitShareholders): Observable<ISubmitShareholders> {
    return this.http.post<ISubmitShareholders>(this.url.submitShareholdersURL(), data);
  }
  annualShareRegisterSubmit(data: ISubmitShareRegisters): Observable<ISubmitShareRegisters> {
    return this.http.post<ISubmitShareRegisters>(this.url.submitShareRegisterURL(), data);
  }
  annualRecordsSubmit(data: ISubmitAnnualRecords): Observable<ISubmitAnnualRecords> {
    return this.http.post<ISubmitAnnualRecords>(this.url.submitAnnualRecordURL(), data);
  }
  annualAuditorSubmit(data: ISubmitAnnualAuditors): Observable<ISubmitAnnualAuditors> {
    return this.http.post<ISubmitAnnualAuditors>(this.url.submitAnnualAuditorRecordURL(), data);
  }
  annualChargeSubmit(data: ISubmitAnnualCharges): Observable<ISubmitAnnualCharges> {
    return this.http.post<ISubmitAnnualCharges>(this.url.submitAnnualChargeRecordURL(), data);
  }
  shareRecordSubmit(data: ISubmitShareRecords): Observable<ISubmitShareRecords> {
    return this.http.post<ISubmitShareRecords>(this.url.submitShareRecordURL(), data);
  }
  removeDoc(data: IremoveAnnualDoc): Observable<IremoveAnnualDoc> {
    return this.http.post<IremoveAnnualDoc>(this.url.removeDocsURL(), data);
  }
  resubmitProcess(data: Iresubmit): Observable<Iresubmit> {
    return this.http.post<Iresubmit>(this.url.resubmitProcess(), data);
  }

  removeExistingDirector(data: IRemoveExistingDirector): Observable<IRemoveExistingDirector> {
    return this.http.post<IRemoveExistingDirector>(this.url.removeExisingDirector(), data);
  }
  removeChangeDirector(data: IRemoveExistingDirector): Observable<IRemoveExistingDirector> {
    return this.http.post<IRemoveExistingDirector>(this.url.removeChangeDirector(), data);
  }
  updateExistingDirector(data: IUpdateExistingDirector): Observable<IUpdateExistingDirector> {
    return this.http.post<IUpdateExistingDirector>(this.url.updateExisingDirector(), data);
  }
  addNewDirector(data: IAddNewDirector): Observable<IAddNewDirector> {
    return this.http.post<IAddNewDirector>(this.url.addNewDirector(), data);
  }

  removeExistingSec(data: IRemoveExistingSec): Observable<IRemoveExistingSec> {
    return this.http.post<IRemoveExistingSec>(this.url.removeExisitingSec(), data);
  }
  updateExistingSec(data: IUpdateExistingSec): Observable<IUpdateExistingSec> {
    return this.http.post<IUpdateExistingSec>(this.url.updateExistingSec(), data);
  }

  removeChangeSec(data: IRemoveExistingSec): Observable<IRemoveExistingSec> {
    return this.http.post<IRemoveExistingSec>(this.url.removeChangeSec(), data);
  }

  updateExistingSecFirm(data: IUpdateExistingSec): Observable<IUpdateExistingSec> {
    return this.http.post<IUpdateExistingSec>(this.url.updateExisitingSecFirm(), data);
  }

  addNewSec(data: IAddNewSec): Observable<IAddNewSec> {
    return this.http.post<IAddNewSec>(this.url.addNewSec(), data);
  }

  addNewSecFirm(data: IAddNewSec): Observable<IAddNewSec> {
    return this.http.post<IAddNewSec>(this.url.addNewSecFirm(), data);
  }

  updateOtherDocDate(data: IUpdateOtherDocDate): Observable<IUpdateOtherDocDate> {
    return this.http.post<IUpdateOtherDocDate>(this.url.updateOtherDocDate(), data);
  }

  updateAlterationType(data: IUpdateAlterType): Observable<IUpdateAlterType> {
    return this.http.post<IUpdateAlterType>(this.url.updateAlterType(), data);
  }

  removeOtherDoc(data: IRemoveOtherDoc): Observable<IRemoveOtherDoc> {
    return this.http.post<IRemoveOtherDoc>(this.url.removeOtherDocsURL(), data);
  }

  updateCourtDetails(data: IupdateCourtDetails): Observable<IupdateCourtDetails> {
    return this.http.post<IupdateCourtDetails>(this.url.updateCourtURL(), data);
  }
}

