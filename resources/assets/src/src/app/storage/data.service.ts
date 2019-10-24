import { IGNdivision, ICountry } from './../http/models/general.model';
import { Injectable } from '@angular/core';
import { onIReg } from '../http/models/register.model';
import { ITitle, IProvince, IDistrict, ICity, IPayment } from '../http/models/general.model';

@Injectable({
  providedIn: 'root'
})
export class DataService {

  public regData: onIReg;
  public storage: any;
  public storage1: any;
  public storage2: any;
  public file: any;

  public ititles: ITitle;
  public provinces: IProvince;
  public districts: IDistrict;
  public citys: ICity;
  public gndivisions: IGNdivision;
  public payment: IPayment;
  public country: Array<ICountry> = [];
  public id;


  private formData: Array<any> = [];

  setLocaldata(ResName: string, data: any) {
    localStorage.setItem(ResName, JSON.stringify(data));
  }

  getLocalData(ResName: string) {
    return JSON.parse(localStorage.getItem(ResName));
  }

  isLocalData(ResName: string) {
    return localStorage.getItem(ResName) === null;
  }

  outLocalData(ResName: string) {
    return localStorage.removeItem(ResName);
  }

  push(data: any) {
    this.formData.push(data);
  }

  get getFormData() {
    return this.formData;
  }

  get getFile() {
    return this.file;
  }

  cleanData() {
    this.formData = [];
  }

  setId(id) {
    this.id = id;
  }

  public get getId() {
    return this.id;
  }

}
