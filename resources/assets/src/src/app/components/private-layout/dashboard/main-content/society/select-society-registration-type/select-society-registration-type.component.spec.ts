import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SelectSocietyRegistrationTypeComponent } from './select-society-registration-type.component';

describe('SelectSocietyRegistrationTypeComponent', () => {
  let component: SelectSocietyRegistrationTypeComponent;
  let fixture: ComponentFixture<SelectSocietyRegistrationTypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SelectSocietyRegistrationTypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelectSocietyRegistrationTypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
