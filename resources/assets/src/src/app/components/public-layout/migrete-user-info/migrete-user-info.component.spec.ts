import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MigreteUserInfoComponent } from './migrete-user-info.component';

describe('MigreteUserInfoComponent', () => {
  let component: MigreteUserInfoComponent;
  let fixture: ComponentFixture<MigreteUserInfoComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MigreteUserInfoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MigreteUserInfoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
